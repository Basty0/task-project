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

#[Description('Met à jour le titre et/ou la description Markdown d\'un ticket. Pour changer uniquement l\'étape, préférer move_ticket.')]
#[IsIdempotent]
class UpdateTicket extends Tool
{
    use InteractsWithTicketBoard;

    protected string $name = 'update_ticket';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $this->resolveUser($request);

        if ($user instanceof Response) {
            return $user;
        }

        $validated = $request->validate([
            'ticket_id' => ['required', 'integer', 'min:1'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'stage' => ['nullable', 'string', $this->stageRule()],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $ticket = $this->findOwnedTicket($user, (int) $validated['ticket_id']);

        if ($ticket instanceof Response) {
            return $ticket;
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
            $ticket->update($updates);
        }

        return $this->jsonResult([
            'message' => 'Ticket mis à jour.',
            'ticket' => $this->ticketPayload($ticket->fresh()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ticket_id' => $schema->integer()->description('ID du ticket')->required(),
            'title' => $schema->string()->description('Nouveau titre'),
            'description' => $schema->string()->description('Nouvelle description Markdown'),
            'stage' => $schema->string()->description('Nouvelle étape (optionnel)'),
            'position' => $schema->integer()->description('Nouvelle position dans la colonne'),
        ];
    }
}
