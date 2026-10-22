<?php

namespace App\Mcp\Tools;

use App\Enums\Stage;
use App\Mcp\Concerns\InteractsWithTicketBoard;
use App\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Liste les sous-tâches d\'un ticket, optionnellement filtrées par étape.')]
#[IsReadOnly]
class ListTasks extends Tool
{
    use InteractsWithTicketBoard;

    protected string $name = 'list_tasks';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $this->resolveUser($request);

        if ($user instanceof Response) {
            return $user;
        }

        $validated = $request->validate([
            'ticket_id' => ['required', 'integer', 'min:1'],
            'stage' => ['nullable', 'string', $this->stageRule()],
        ]);

        $ticket = $this->findOwnedTicket($user, (int) $validated['ticket_id']);

        if ($ticket instanceof Response) {
            return $ticket;
        }

        $query = $ticket->tasks()->orderBy('position')->orderBy('id');

        if (! empty($validated['stage'])) {
            $query->where('stage', Stage::from($validated['stage']));
        }

        $tasks = $query->get()->map(fn (Task $task): array => $this->taskPayload($task))->values()->all();

        return $this->jsonResult([
            'ticket_id' => $ticket->id,
            'count' => count($tasks),
            'tasks' => $tasks,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ticket_id' => $schema->integer()->description('ID du ticket parent')->required(),
            'stage' => $schema->string()->description('Filtrer par étape'),
        ];
    }
}
