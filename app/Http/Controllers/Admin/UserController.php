<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->orderByDesc('is_admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'is_admin', 'created_at']);

        return Inertia::render('users/index', [
            'users' => $users,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('users.index');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->withErrors(['user' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        if ($user->is_admin) {
            return redirect()
                ->route('users.index')
                ->withErrors(['user' => 'Impossible de supprimer un compte administrateur.']);
        }

        $user->delete();

        return redirect()->route('users.index');
    }
}
