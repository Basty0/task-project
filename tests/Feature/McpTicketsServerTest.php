<?php

use App\Enums\Stage;
use App\Mcp\Servers\TicketsServer;
use App\Mcp\Tools\CreateTask;
use App\Mcp\Tools\CreateTicket;
use App\Mcp\Tools\GetTicket;
use App\Mcp\Tools\ListTickets;
use App\Mcp\Tools\MoveTicket;
use App\Models\Ticket;
use App\Models\User;

test('mcp list tickets only returns the authenticated users tickets', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    Ticket::factory()->create(['user_id' => $alice->id, 'title' => 'Alice ticket']);
    Ticket::factory()->create(['user_id' => $bob->id, 'title' => 'Bob ticket']);

    TicketsServer::actingAs($alice)
        ->tool(ListTickets::class, [])
        ->assertOk()
        ->assertSee('Alice ticket')
        ->assertDontSee('Bob ticket');
});

test('mcp agent can fully manage owned tickets and tasks', function () {
    $user = User::factory()->create();

    $createTicket = TicketsServer::actingAs($user)->tool(CreateTicket::class, [
        'title' => 'Ticket MCP',
        'description' => '## Contenu',
        'stage' => Stage::Todo->value,
    ]);

    $createTicket->assertOk()->assertSee('Ticket MCP');

    $ticket = Ticket::query()->where('user_id', $user->id)->where('title', 'Ticket MCP')->firstOrFail();

    TicketsServer::actingAs($user)
        ->tool(CreateTask::class, [
            'ticket_id' => $ticket->id,
            'title' => 'Sous-tâche MCP',
            'stage' => Stage::InProgress->value,
        ])
        ->assertOk()
        ->assertSee('Sous-tâche MCP');

    TicketsServer::actingAs($user)
        ->tool(MoveTicket::class, [
            'ticket_id' => $ticket->id,
            'stage' => Stage::WaitingClarification->value,
        ])
        ->assertOk();

    expect($ticket->fresh()->stage)->toBe(Stage::WaitingClarification);

    TicketsServer::actingAs($user)
        ->tool(GetTicket::class, ['ticket_id' => $ticket->id])
        ->assertOk()
        ->assertSee('Sous-tâche MCP');
});

test('mcp cannot access another users ticket', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    $bobTicket = Ticket::factory()->create(['user_id' => $bob->id]);

    TicketsServer::actingAs($alice)
        ->tool(GetTicket::class, ['ticket_id' => $bobTicket->id])
        ->assertSee('introuvable');
});
