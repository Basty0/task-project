<?php

use App\Models\Ticket;
use App\Models\User;

test('guests cannot manage users', function () {
    $this->get(route('users.index'))->assertRedirect(route('login'));
});

test('non admin users cannot manage users', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('admin can view users page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('users/index')->has('users'));
});

test('admin can create a user', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('users.store'), [
            'name' => 'Nouveau',
            'email' => 'nouveau@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'nouveau@example.com',
        'is_admin' => false,
    ]);
});

test('non admin cannot create a user', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'Intrus',
            'email' => 'intrus@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertForbidden();
});

test('admin can delete a non admin user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($admin)
        ->delete(route('users.destroy', $user))
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admin cannot delete another admin', function () {
    $admin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $otherAdmin))
        ->assertRedirect(route('users.index'))
        ->assertSessionHasErrors('user');

    $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
});

test('users only see their own tickets on dashboard', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    Ticket::factory()->create(['user_id' => $alice->id, 'title' => 'Ticket Alice']);
    Ticket::factory()->create(['user_id' => $bob->id, 'title' => 'Ticket Bob']);

    $this->actingAs($alice)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('ticketsByStage.todo.0.title', 'Ticket Alice')
            ->has('ticketsByStage.todo', 1));
});
