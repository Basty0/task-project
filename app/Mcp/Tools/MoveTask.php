<?php

namespace App\Mcp\Tools;

use App\Enums\Stage;
use App\Mcp\Concerns\InteractsWithTicketBoard;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Déplace une sous-tâche vers une autre étape du parcours (équivalent drag & drop).')]
class MoveTask extends Tool
{
    use InteractsWithTicketBoard;

    protected string $name = 'move_task';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $this->resolveUser($request);

        if ($user instanceof Response) {
            return $user;
        }

        $validated = $request->validate([
            'ticket_id' => ['required', 'integer', 'min:1'],
            'task_id' => ['required', 'integer', 'min:1'],
            'stage' => ['required', 'string', $this->stageRule()],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $task = $this->findOwnedTask($user, (int) $validated['ticket_id'], (int) $validated['task_id']);

        if ($task instanceof Response) {
            return $task;
        }

        $ticket = $task->ticket;
        $stage = Stage::from($validated['stage']);
        $position = array_key_exists('position', $validated)
            ? (int) $validated['position']
            : $this->nextTaskPosition($ticket, $stage);

        $task->update([
            'stage' => $stage,
            'position' => $position,
        ]);

        return $this->jsonResult([
            'message' => 'Sous-tâche déplacée.',
            'task' => $this->taskPayload($task->fresh()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ticket_id' => $schema->integer()->description('ID du ticket parent')->required(),
            'task_id' => $schema->integer()->description('ID de la sous-tâche')->required(),
            'stage' => $schema->string()->description('Étape cible')->required(),
            'position' => $schema->integer()->description('Position dans la colonne'),
        ];
    }
}
