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
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Description('Met à jour le titre et/ou la description Markdown d\'une sous-tâche.')]
#[IsIdempotent]
class UpdateTask extends Tool
{
    use InteractsWithTicketBoard;

    protected string $name = 'update_task';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $this->resolveUser($request);

        if ($user instanceof Response) {
            return $user;
        }

        $validated = $request->validate([
            'ticket_id' => ['required', 'integer', 'min:1'],
            'task_id' => ['required', 'integer', 'min:1'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'stage' => ['nullable', 'string', $this->stageRule()],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $task = $this->findOwnedTask($user, (int) $validated['ticket_id'], (int) $validated['task_id']);

        if ($task instanceof Response) {
            return $task;
        }

        $updates = [];

        if (array_key_exists('title', $validated)) {
            $updates['title'] = $validated['title'];
        }

        if (array_key_exists('description', $validated)) {
            $updates['description'] = $validated['description'];
        }

        if (! empty($validated['stage'])) {
            $updates['stage'] = Stage::from($validated['stage']);
        }

        if (array_key_exists('position', $validated)) {
            $updates['position'] = $validated['position'];
        }

        if ($updates !== []) {
            $task->update($updates);
        }

        return $this->jsonResult([
            'message' => 'Sous-tâche mise à jour.',
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
            'title' => $schema->string()->description('Nouveau titre'),
            'description' => $schema->string()->description('Nouvelle description Markdown'),
            'stage' => $schema->string()->description('Nouvelle étape'),
            'position' => $schema->integer()->description('Nouvelle position'),
        ];
    }
}
