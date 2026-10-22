<?php

use App\Http\Middleware\AuthenticateMcpToken;
use App\Mcp\Servers\TicketsServer;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| MCP Tickets — espace personnel par token
|--------------------------------------------------------------------------
|
| Chaque utilisateur génère son token dans Paramètres > MCP Agent,
| puis le donne à son agent (Authorization: Bearer <token>).
| L'agent ne peut gérer que les tickets / sous-tâches de ce compte.
|
*/

Mcp::local('tickets', TicketsServer::class);

Mcp::web('/mcp/tickets', TicketsServer::class)
    ->middleware([AuthenticateMcpToken::class]);
