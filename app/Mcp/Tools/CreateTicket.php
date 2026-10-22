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

#[Description('Crée un ticket sur le board. La description peut être en Markdown. Stage par défaut: todo.')]
class CreateTicket extends Tool
{
    use InteractsWithTicketBoard;

    protected string $name = 'create_ticket';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $this->resolveUser($request);

        if ($user instanceof Response) {
            return $user;
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'stage' => ['nullable', 'string', $this->stageRule()],
        ]);

        $stage = Stage::tryFrom($validated['stage'] ?? '') ?? Stage::Todo;

        $ticket = $user->tickets()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'stage' => $stage,
            'position' => $this->nextTicketPosition($user, $stage),
        ]);

        return $this->jsonResult([
            'message' => 'Ticket créé.',
            'ticket' => $this->ticketPayload($ticket),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Titre du ticket')->required(),
            'description' => $schema->string()->description('Description Markdown (optionnel)'),
            'stage' => $schema->string()->description('Étape initiale (défaut: todo)'),
        ];
    }
}
