<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mcp_token_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'mcp_token_created_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * @return HasMany<Ticket, $this>
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function generateMcpToken(): string
    {
        $plainTextToken = 'mcp_'.Str::random(48);

        $this->forceFill([
            'mcp_token_hash' => hash('sha256', $plainTextToken),
            'mcp_token_prefix' => substr($plainTextToken, 0, 12),
            'mcp_token_created_at' => now(),
        ])->save();

        return $plainTextToken;
    }

    public function revokeMcpToken(): void
    {
        $this->forceFill([
            'mcp_token_hash' => null,
            'mcp_token_prefix' => null,
            'mcp_token_created_at' => null,
        ])->save();
    }

    public static function findByMcpToken(string $plainTextToken): ?self
    {
        if ($plainTextToken === '') {
            return null;
        }

        return static::query()
            ->where('mcp_token_hash', hash('sha256', $plainTextToken))
            ->first();
    }
}
