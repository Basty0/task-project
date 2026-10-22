<?php

namespace App\Http\Controllers;

use App\Enums\Stage;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, Ticket $ticket): RedirectResponse
    {
        $stage = Stage::tryFrom($request->validated('stage') ?? '') ?? Stage::Todo;

        $position = Task::query()
            ->where('ticket_id', $ticket->id)
            ->where('stage', $stage)
            ->max('position');

        $ticket->tasks()->create([
            ...$request->safe()->only(['title', 'description']),
            'stage' => $stage,
            'position' => is_null($position) ? 0 : $position + 1,
        ]);

        return redirect()->back();
    }

    public function update(UpdateTaskRequest $request, Ticket $ticket, Task $task): RedirectResponse
    {
        abort_unless($task->ticket_id === $ticket->id, 404);

        $task->update($request->safe()->only(['title', 'description', 'stage', 'position']));

        return redirect()->back();
    }

    public function destroy(Ticket $ticket, Task $task): RedirectResponse
    {
        abort_unless($task->ticket_id === $ticket->id, 404);

        Gate::authorize('update', $ticket);

        $task->delete();

        return redirect()->back();
    }
}
