<?php

namespace App\Mcp\Prompts;

use App\Mcp\Concerns\InteractsWithTicketBoard;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Description('Prépare un prompt de triage pour un ticket: résumé, étape suggérée, et prochaines sous-tâches.')]
class TriageTicketPrompt extends Prompt
{
    use InteractsWithTicketBoard;

    protected string $name = 'triage_ticket';

    public function handle(Request $request): Response
    {
        $user = $this->resolveUser($request);

        if ($user instanceof Response) {
            return $user;
        }

        $validated = $request->validate([
            'ticket_id' => ['required', 'integer', 'min:1'],
        ]);

        $ticket = $this->findOwnedTicket($user, (int) $validated['ticket_id']);

        if ($ticket instanceof Response) {
            return $ticket;
        }

        $ticket->load(['tasks' => fn ($query) => $query->orderBy('position')->orderBy('id')]);
        $payload = $this->ticketPayload($ticket, withTasks: true);

        $markdown = <<<MARKDOWN
# Triage du ticket #{$payload['id']}

Tu es un agent de gestion de tickets Kanban.

## Ticket
- **Titre**: {$payload['title']}
- **Étape actuelle**: {$payload['stage']} ({$payload['stage_label']})
- **Description**:
{$payload['description']}

## Sous-tâches
```json
{$this->encodeTasks($payload['tasks'])}
```

## Mission
1. Résume le ticket en 3 puces.
2. Propose l'étape la plus pertinente parmi: todo, waiting_clarification, in_progress, done, abandoned.
3. Propose 1 à 3 sous-tâches concrètes (titre + étape).
4. Indique les tools MCP à appeler ensuite (`move_ticket`, `create_task`, etc.) avec les arguments exacts.
MARKDOWN;

        return Response::text($markdown);
    }

    /**
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument('ticket_id', 'ID du ticket à triage', required: true),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $tasks
     */
    private function encodeTasks(array $tasks): string
    {
        return json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }
}
