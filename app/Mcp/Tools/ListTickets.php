<?php

namespace App\Mcp\Tools;

use App\Enums\Stage;
use App\Mcp\Concerns\InteractsWithTicketBoard;
use App\Models\Ticket;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Liste les tickets du board Kanban, optionnellement filtrés par étape. Retourne id, titre, stage, position.')]
#[IsReadOnly]
class ListTickets extends Tool
{
    use InteractsWithTicketBoard;

    protected string $name = 'list_tickets';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $this->resolveUser($request);

        if ($user instanceof Response) {
            return $user;
        }

        $validated = $request->validate([
            'stage' => ['nullable', 'string', $this->stageRule()],
        ]);

        $query = Ticket::query()
            ->where('user_id', $user->id)
            ->orderBy('stage')
            ->orderBy('position')
            ->orderBy('id');

        if (! empty($validated['stage'])) {
            $query->where('stage', Stage::from($validated['stage']));
        }

        $tickets = $query->get()->map(fn (Ticket $ticket): array => $this->ticketPayload($ticket))->values()->all();

        return $this->jsonResult([
            'count' => count($tickets),
            'tickets' => $tickets,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'stage' => $schema->string()->description('Filtrer par étape: todo, waiting_clarification, in_progress, done, abandoned'),
        ];
    }
}
