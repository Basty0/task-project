<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\TriageTicketPrompt;
use App\Mcp\Resources\TicketBoardResource;
use App\Mcp\Tools\CreateTask;
use App\Mcp\Tools\CreateTicket;
use App\Mcp\Tools\DeleteTask;
use App\Mcp\Tools\DeleteTicket;
use App\Mcp\Tools\GetTicket;
use App\Mcp\Tools\ListStages;
use App\Mcp\Tools\ListTasks;
use App\Mcp\Tools\ListTickets;
use App\Mcp\Tools\MoveTask;
use App\Mcp\Tools\MoveTicket;
use App\Mcp\Tools\UpdateTask;
use App\Mcp\Tools\UpdateTicket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Tickets Kanban')]
#[Version('1.1.0')]
#[Instructions(<<<'MARKDOWN'
# Tickets Kanban MCP (espace personnel)

Ce serveur est **scopé à l'utilisateur authentifié par token MCP**.
L'agent ne peut voir ni modifier que les tickets et sous-tâches de ce compte.

## Auth
- HTTP : `Authorization: Bearer <mcp_token>`
- Local (stdio) : variable d'environnement `MCP_TOKEN=<mcp_token>`

## Parcours (stages)
- `todo` — À faire
- `waiting_clarification` — En attente de précision
- `in_progress` — En cours
- `done` — Terminé
- `abandoned` — Abandonné

## Workflow agent
1. `list_stages` si besoin
2. `list_tickets` ou resource `ticket://board`
3. `get_ticket` pour le détail + sous-tâches
4. CRUD / move via `create_*`, `update_*`, `move_*`, `delete_*`
5. Descriptions en **Markdown**
MARKDOWN)]
class TicketsServer extends Server
{
    /**
     * @var array<int, class-string>
     */
    protected array $tools = [
        ListStages::class,
        ListTickets::class,
        GetTicket::class,
        CreateTicket::class,
        UpdateTicket::class,
        MoveTicket::class,
        DeleteTicket::class,
        ListTasks::class,
        CreateTask::class,
        UpdateTask::class,
        MoveTask::class,
        DeleteTask::class,
    ];

    /**
     * @var array<int, class-string>
     */
    protected array $resources = [
        TicketBoardResource::class,
    ];

    /**
     * @var array<int, class-string>
     */
    protected array $prompts = [
        TriageTicketPrompt::class,
    ];

    protected function boot(): void
    {
        if (Auth::check()) {
            return;
        }

        $token = env('MCP_TOKEN');

        if (! is_string($token) || $token === '') {
            return;
        }

        $user = User::findByMcpToken($token);

        if ($user instanceof User) {
            Auth::login($user);
        }
    }
}
