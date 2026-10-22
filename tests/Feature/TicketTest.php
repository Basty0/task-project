<?php

use App\Enums\Stage;
use App\Models\Ticket;
use App\Models\User;

test('guests cannot view tickets', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated users can view their tickets board', function () {
    $user = User::factory()->create();
    Ticket::factory()->create(['user_id' => $user->id, 'title' => 'Mon ticket']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('ticketsByStage.todo', 1)
            ->where('ticketsByStage.todo.0.title', 'Mon ticket')
            ->has('stages'));
});

test('authenticated users can create a ticket', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tickets.store'), [
            'title' => 'Nouveau ticket',
            'description' => 'Description',
            'stage' => Stage::Todo->value,
        ])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('tickets', [
        'user_id' => $user->id,
        'title' => 'Nouveau ticket',
        'stage' => Stage::Todo->value,
    ]);
});

test('ticket title is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tickets.store'), [
            'title' => '',
        ])
        ->assertSessionHasErrors('title');
});

test('authenticated users can move a ticket between stages', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create([
        'user_id' => $user->id,
        'stage' => Stage::Todo,
        'position' => 0,
    ]);

    $this->actingAs($user)
        ->patch(route('tickets.update', $ticket), [
            'stage' => Stage::InProgress->value,
            'position' => 0,
        ])
        ->assertRedirect();

    expect($ticket->fresh()->stage)->toBe(Stage::InProgress)
        ->and($ticket->fresh()->position)->toBe(0);
});

test('authenticated users can move a ticket to waiting clarification or abandoned', function () {
    $user = User::factory()->create();
    $waiting = Ticket::factory()->create(['user_id' => $user->id, 'stage' => Stage::Todo]);
    $abandoned = Ticket::factory()->create(['user_id' => $user->id, 'stage' => Stage::InProgress]);

    $this->actingAs($user)
        ->patch(route('tickets.update', $waiting), [
            'stage' => Stage::WaitingClarification->value,
            'position' => 0,
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->patch(route('tickets.update', $abandoned), [
            'stage' => Stage::Abandoned->value,
            'position' => 0,
        ])
        ->assertRedirect();

    expect($waiting->fresh()->stage)->toBe(Stage::WaitingClarification)
        ->and($abandoned->fresh()->stage)->toBe(Stage::Abandoned);
});

test('tickets board exposes all workflow stages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('stages.todo')
            ->has('stages.waiting_clarification')
            ->has('stages.in_progress')
            ->has('stages.done')
            ->has('stages.abandoned')
            ->has('ticketsByStage.waiting_clarification')
            ->has('ticketsByStage.abandoned'));
});

test('authenticated users can view a ticket board', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $user->id, 'title' => 'Détail']);

    $this->actingAs($user)
        ->get(route('tickets.show', $ticket))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('tickets/show')
            ->where('ticket.title', 'Détail')
            ->has('tasksByStage')
            ->has('stages'));
});

test('users cannot view another users ticket', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->get(route('tickets.show', $ticket))
        ->assertForbidden();
});

test('users cannot update another users ticket', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->patch(route('tickets.update', $ticket), [
            'title' => 'Hack',
        ])
        ->assertForbidden();
});

test('authenticated users can delete their ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->delete(route('tickets.destroy', $ticket))
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
});
