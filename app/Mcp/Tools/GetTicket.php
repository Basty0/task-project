<?php

namespace App\Mcp\Tools;

use App\Mcp\Concerns\InteractsWithTicketBoard;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Récupère un ticket par id avec sa description Markdown et toutes ses sous-tâches groupées.')]
#[IsReadOnly]
class GetTicket extends Tool
{
    use InteractsWithTicketBoard;

    protected string $name = 'get_ticket';

    public function handle(Request $request): Response|ResponseFactory
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

        return $this->jsonResult([
            'ticket' => $this->ticketPayload($ticket, withTasks: true),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ticket_id' => $schema->integer()->description('ID du ticket')->required(),
        ];
    }
}
