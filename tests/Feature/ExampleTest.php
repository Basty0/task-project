<?php

use App\Models\User;

it('redirects home to login for guests', function () {
    $this->get('/')->assertRedirect(route('login'));
});

it('redirects home to dashboard for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect(route('dashboard'));
});
