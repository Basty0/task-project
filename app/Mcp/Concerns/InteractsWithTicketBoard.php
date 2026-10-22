<?php

namespace App\Mcp\Concerns;

use App\Enums\Stage;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

trait InteractsWithTicketBoard
{
    protected function resolveUser(Request $request): User|Response
    {
        $authenticated = $request->user();

        if ($authenticated instanceof User) {
            return $authenticated;
        }

        return Response::error(
            'Non authentifié. Fournissez un token MCP valide (Authorization: Bearer <token> ou MCP_TOKEN en local).'
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function ticketPayload(Ticket $ticket, bool $withTasks = false): array
    {
        $payload = [
            'id' => $ticket->id,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'stage' => $ticket->stage->value,
            'stage_label' => $ticket->stage->label(),
            'position' => $ticket->position,
            'updated_at' => $ticket->updated_at?->toIso8601String(),
        ];

        if ($withTasks) {
            $payload['tasks'] = $ticket->tasks
                ->map(fn (Task $task): array => $this->taskPayload($task))
                ->values()
                ->all();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    protected function taskPayload(Task $task): array
    {
        return [
            'id' => $task->id,
            'ticket_id' => $task->ticket_id,
            'title' => $task->title,
            'description' => $task->description,
            'stage' => $task->stage->value,
            'stage_label' => $task->stage->label(),
            'position' => $task->position,
            'updated_at' => $task->updated_at?->toIso8601String(),
        ];
    }

    protected function jsonResult(array $payload): ResponseFactory
    {
        return Response::structured($payload);
    }

    protected function stageRule(): string
    {
        return 'in:'.implode(',', Stage::values());
    }

    protected function findOwnedTicket(User $user, int $ticketId): Ticket|Response
    {
        $ticket = Ticket::query()
            ->where('user_id', $user->id)
            ->whereKey($ticketId)
            ->first();

        if (! $ticket instanceof Ticket) {
            return Response::error("Ticket [{$ticketId}] introuvable pour cet utilisateur.");
        }

        return $ticket;
    }

    protected function findOwnedTask(User $user, int $ticketId, int $taskId): Task|Response
    {
        $ticket = $this->findOwnedTicket($user, $ticketId);

        if ($ticket instanceof Response) {
            return $ticket;
        }

        $task = $ticket->tasks()->whereKey($taskId)->first();

        if (! $task instanceof Task) {
            return Response::error("Tâche [{$taskId}] introuvable sur le ticket [{$ticketId}].");
        }

        return $task;
    }

    protected function nextTicketPosition(User $user, Stage $stage): int
    {
        $position = Ticket::query()
            ->where('user_id', $user->id)
            ->where('stage', $stage)
            ->max('position');

        return is_null($position) ? 0 : $position + 1;
    }

    protected function nextTaskPosition(Ticket $ticket, Stage $stage): int
    {
        $position = Task::query()
            ->where('ticket_id', $ticket->id)
            ->where('stage', $stage)
            ->max('position');

        return is_null($position) ? 0 : $position + 1;
    }
}
