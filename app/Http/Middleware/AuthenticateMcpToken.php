<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMcpToken
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! is_string($token) || $token === '') {
            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32001,
                    'message' => 'Token MCP manquant. Ajoutez Authorization: Bearer <token>.',
                ],
                'id' => null,
            ], 401);
        }

        $user = User::findByMcpToken($token);

        if (! $user instanceof User) {
            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32001,
                    'message' => 'Token MCP invalide.',
                ],
                'id' => null,
            ], 401);
        }

        Auth::login($user);

        return $next($request);
    }
}
