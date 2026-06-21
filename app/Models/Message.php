<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'body',
        'type',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime',
        'attachment_size',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    public function reactionSummary(): array
    {
        $summary = array_fill_keys(Reaction::kinds(), 0);

        $source = $this->relationLoaded('reactions')
            ? $this->reactions
            : $this->reactions()->get(['user_id', 'kind']);

        foreach ($source as $reaction) {
            if (array_key_exists($reaction->kind, $summary)) {
                $summary[$reaction->kind]++;
            }
        }

        return $summary;
    }

    public function currentReactionKindFor(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $source = $this->relationLoaded('reactions')
            ? $this->reactions
            : $this->reactions()->get(['user_id', 'kind']);

        return optional($source->firstWhere('user_id', $user->id))->kind;
    }

    public function attachmentUrl(): ?string
    {
        return $this->attachment_path ? asset('storage/'.$this->attachment_path) : null;
    }

    public function isImageAttachment(): bool
    {
        return str_starts_with((string) $this->attachment_mime, 'image/');
    }

    public function isAudioAttachment(): bool
    {
        return str_starts_with((string) $this->attachment_mime, 'audio/');
    }
}
