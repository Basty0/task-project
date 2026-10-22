<?php

namespace App\Mcp\Resources;

use App\Enums\Stage;
use App\Mcp\Concerns\InteractsWithTicketBoard;
use App\Models\Ticket;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Uri('ticket://board')]
#[MimeType('application/json')]
#[Description('Snapshot JSON du board Kanban courant (tickets groupés par étape).')]
class TicketBoardResource extends Resource
{
    use InteractsWithTicketBoard;

    protected string $name = 'ticket_board';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $this->resolveUser($request);

        if ($user instanceof Response) {
            return $user;
        }

        $tickets = Ticket::query()
            ->where('user_id', $user->id)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $board = [];

        foreach (Stage::cases() as $stage) {
            $board[$stage->value] = [
                'label' => $stage->label(),
                'tickets' => $tickets
                    ->filter(fn (Ticket $ticket): bool => $ticket->stage === $stage)
                    ->values()
                    ->map(fn (Ticket $ticket): array => $this->ticketPayload($ticket))
                    ->all(),
            ];
        }

        return $this->jsonResult([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ],
            'board' => $board,
        ]);
    }
}
