<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'interest_tags',
        'role',
        'notify_on_message',
        'notify_on_follow',
        'notify_on_like',
        'notify_on_comment',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'interest_tags' => 'array',
        'notify_on_message' => 'bool',
        'notify_on_follow' => 'bool',
        'notify_on_like' => 'bool',
        'notify_on_comment' => 'bool',
    ];

    public static function availableRoles(): array
    {
        return [
            'admin' => 'Администратор',
            'moderator' => 'Модератор',
            'participant' => 'Участник',
        ];
    }

    public function normalizedRole(): string
    {
        return $this->role === 'user' || $this->role === null
            ? 'participant'
            : $this->role;
    }

    public function roleLabel(): string
    {
        return self::availableRoles()[$this->normalizedRole()] ?? 'Участник';
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->normalizedRole(), $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isModerator(): bool
    {
        return $this->hasRole('moderator');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'follows',
            'follower_id',
            'following_id'
        )->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'follows',
            'following_id',
            'follower_id'
        )->withTimestamps();
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function follows(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function avatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=000&color=fff';
    }
}
