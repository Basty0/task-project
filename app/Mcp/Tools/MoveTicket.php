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

#[Description('Déplace un ticket vers une autre étape du parcours Kanban (équivalent drag & drop).')]
class MoveTicket extends Tool
{
    use InteractsWithTicketBoard;

    protected string $name = 'move_ticket';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $this->resolveUser($request);

        if ($user instanceof Response) {
            return $user;
        }

        $validated = $request->validate([
            'ticket_id' => ['required', 'integer', 'min:1'],
            'stage' => ['required', 'string', $this->stageRule()],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $ticket = $this->findOwnedTicket($user, (int) $validated['ticket_id']);

        if ($ticket instanceof Response) {
            return $ticket;
        }

        $stage = Stage::from($validated['stage']);
        $position = array_key_exists('position', $validated)
            ? (int) $validated['position']
            : $this->nextTicketPosition($user, $stage);

        $ticket->update([
            'stage' => $stage,
            'position' => $position,
        ]);

        return $this->jsonResult([
            'message' => 'Ticket déplacé.',
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
            'stage' => $schema->string()->description('Étape cible')->required(),
            'position' => $schema->integer()->description('Position dans la colonne (défaut: fin de colonne)'),
        ];
    }
}
