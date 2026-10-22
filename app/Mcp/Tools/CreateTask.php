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

#[Description('Crée une sous-tâche sur un ticket. Description Markdown optionnelle. Stage par défaut: todo.')]
class CreateTask extends Tool
{
    use InteractsWithTicketBoard;

    protected string $name = 'create_task';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $this->resolveUser($request);

        if ($user instanceof Response) {
            return $user;
        }

        $validated = $request->validate([
            'ticket_id' => ['required', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'stage' => ['nullable', 'string', $this->stageRule()],
        ]);

        $ticket = $this->findOwnedTicket($user, (int) $validated['ticket_id']);

        if ($ticket instanceof Response) {
            return $ticket;
        }

        $stage = Stage::tryFrom($validated['stage'] ?? '') ?? Stage::Todo;

        $task = $ticket->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'stage' => $stage,
            'position' => $this->nextTaskPosition($ticket, $stage),
        ]);

        return $this->jsonResult([
            'message' => 'Sous-tâche créée.',
            'task' => $this->taskPayload($task),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ticket_id' => $schema->integer()->description('ID du ticket parent')->required(),
            'title' => $schema->string()->description('Titre de la sous-tâche')->required(),
            'description' => $schema->string()->description('Description Markdown'),
            'stage' => $schema->string()->description('Étape initiale (défaut: todo)'),
        ];
    }
}
