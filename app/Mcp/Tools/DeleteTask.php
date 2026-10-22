<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\InteractsWithTicketBoard;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Description('Supprime une sous-tâche. Irréversible.')]
#[IsDestructive]
class DeleteTask extends Tool
{
    use InteractsWithTicketBoard;

    protected string $name = 'delete_task';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $this->resolveUser($request);

        if ($user instanceof Response) {
            return $user;
        }

        $validated = $request->validate([
            'ticket_id' => ['required', 'integer', 'min:1'],
            'task_id' => ['required', 'integer', 'min:1'],
        ]);

        $task = $this->findOwnedTask($user, (int) $validated['ticket_id'], (int) $validated['task_id']);

        if ($task instanceof Response) {
            return $task;
        }

        $payload = $this->taskPayload($task);
        $task->delete();

        return $this->jsonResult([
            'message' => 'Sous-tâche supprimée.',
            'deleted' => $payload,
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
        ];
    }
}
