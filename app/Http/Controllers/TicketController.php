<?php

namespace App\Http\Controllers;

use App\Enums\Stage;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Ticket::class);

        $tickets = Ticket::query()
            ->where('user_id', auth()->id())
            ->orderBy('position')
            ->get();

        return Inertia::render('tickets/index', [
            'ticketsByStage' => $this->groupByStage($tickets),
            'stages' => Stage::labels(),
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $stage = Stage::tryFrom($request->validated('stage') ?? '') ?? Stage::Todo;

        $position = Ticket::query()
            ->where('user_id', $request->user()->id)
            ->where('stage', $stage)
            ->max('position');

        $request->user()->tickets()->create([
            ...$request->safe()->only(['title', 'description']),
            'stage' => $stage,
            'position' => is_null($position) ? 0 : $position + 1,
        ]);

        return redirect()->route('tickets.index');
    }

    public function show(Ticket $ticket): Response
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['tasks' => fn ($query) => $query->orderBy('position')]);

        return Inertia::render('tickets/show', [
            'ticket' => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'stage' => $ticket->stage->value,
            ],
            'tasksByStage' => $this->groupByStage($ticket->tasks),
            'stages' => Stage::labels(),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $ticket->update($request->safe()->only(['title', 'description', 'stage', 'position']));

        return redirect()->back();
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Ticket|\App\Models\Task>  $items
     * @return array<string, list<array<string, mixed>>>
     */
    private function groupByStage($items): array
    {
        $grouped = [];

        foreach (Stage::cases() as $stage) {
            $grouped[$stage->value] = $items
                ->filter(fn ($item): bool => $item->stage === $stage)
                ->values()
                ->map(fn ($item): array => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'stage' => $item->stage->value,
                    'position' => $item->position,
                ])
                ->all();
        }

        return $grouped;
    }
}
