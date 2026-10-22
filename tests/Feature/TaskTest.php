<?php

use App\Enums\Stage;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;

test('authenticated users can create a task on their ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('tasks.store', $ticket), [
            'title' => 'Sous-tâche',
            'description' => 'À faire',
            'stage' => Stage::Todo->value,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'ticket_id' => $ticket->id,
        'title' => 'Sous-tâche',
        'stage' => Stage::Todo->value,
    ]);
});

test('task title is required', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('tasks.store', $ticket), [
            'title' => '',
        ])
        ->assertSessionHasErrors('title');
});

test('authenticated users can move a task between stages', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create([
        'ticket_id' => $ticket->id,
        'stage' => Stage::Todo,
        'position' => 0,
    ]);

    $this->actingAs($user)
        ->patch(route('tasks.update', [$ticket, $task]), [
            'stage' => Stage::Done->value,
            'position' => 0,
        ])
        ->assertRedirect();

    expect($task->fresh()->stage)->toBe(Stage::Done);
});

test('authenticated users can delete a task', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create(['ticket_id' => $ticket->id]);

    $this->actingAs($user)
        ->delete(route('tasks.destroy', [$ticket, $task]))
        ->assertRedirect();

    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('users cannot create tasks on another users ticket', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->post(route('tasks.store', $ticket), [
            'title' => 'Intrus',
        ])
        ->assertForbidden();
});

test('task update is scoped to its ticket', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->create(['user_id' => $user->id]);
    $otherTicket = Ticket::factory()->create(['user_id' => $user->id]);
    $task = Task::factory()->create(['ticket_id' => $ticket->id]);

    $this->actingAs($user)
        ->patch(route('tasks.update', [$otherTicket, $task]), [
            'title' => 'Mauvais parent',
        ])
        ->assertNotFound();
});
