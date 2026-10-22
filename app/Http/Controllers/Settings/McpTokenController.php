<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class McpTokenController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('settings/mcp', [
            'hasToken' => filled($user->mcp_token_hash),
            'tokenPrefix' => $user->mcp_token_prefix,
            'tokenCreatedAt' => $user->mcp_token_created_at?->toIso8601String(),
            'mcpEndpoint' => url('/mcp/tickets'),
            'plainTextToken' => $request->session()->get('mcp_plain_text_token'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $plainTextToken = $request->user()->generateMcpToken();

        return redirect()
            ->route('mcp.edit')
            ->with('mcp_plain_text_token', $plainTextToken);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->revokeMcpToken();

        return redirect()
            ->route('mcp.edit')
            ->with('status', 'mcp-token-revoked');
    }
}
