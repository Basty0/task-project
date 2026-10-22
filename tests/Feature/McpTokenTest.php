<?php

use App\Models\User;

test('authenticated users can open mcp settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('mcp.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/mcp')
            ->where('hasToken', false)
            ->has('mcpEndpoint'));
});

test('users can generate and regenerate an mcp token', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('mcp.store'))
        ->assertRedirect(route('mcp.edit'))
        ->assertSessionHas('mcp_plain_text_token');

    $user->refresh();

    expect($user->mcp_token_hash)->not->toBeNull()
        ->and($user->mcp_token_prefix)->toStartWith('mcp_');

    $firstHash = $user->mcp_token_hash;

    $this->actingAs($user)
        ->post(route('mcp.store'))
        ->assertRedirect(route('mcp.edit'));

    expect($user->fresh()->mcp_token_hash)->not->toBe($firstHash);
});

test('users can revoke their mcp token', function () {
    $user = User::factory()->create();
    $user->generateMcpToken();

    $this->actingAs($user)
        ->delete(route('mcp.destroy'))
        ->assertRedirect(route('mcp.edit'));

    expect($user->fresh()->mcp_token_hash)->toBeNull();
});

test('mcp endpoint rejects requests without bearer token', function () {
    $this->postJson('/mcp/tickets', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/list',
        'params' => (object) [],
    ])->assertUnauthorized();
});

test('mcp endpoint accepts a valid bearer token', function () {
    $user = User::factory()->create();
    $token = $user->generateMcpToken();

    $this->withToken($token)
        ->postJson('/mcp/tickets', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2025-11-25',
                'capabilities' => (object) [],
                'clientInfo' => [
                    'name' => 'pest',
                    'version' => '1.0.0',
                ],
            ],
        ])
        ->assertOk();
});
