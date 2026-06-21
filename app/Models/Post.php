<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'image',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->whereNull('parent_id')
            ->latest()
            ->with(['user', 'parent.user', 'replies']);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
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

    public function imageUrl(): ?string
    {
        return $this->imageUrls()[0] ?? null;
    }

    public function imagePaths(): array
    {
        $paths = is_array($this->images) ? $this->images : [];

        if ($this->image && ! in_array($this->image, $paths, true)) {
            array_unshift($paths, $this->image);
        }

        return array_values(array_filter($paths));
    }

    public function imageUrls(): array
    {
        return array_values(array_map(
            fn (string $path) => asset('storage/'.$path),
            $this->imagePaths()
        ));
    }

    public function hasGallery(): bool
    {
        return count($this->imagePaths()) > 1;
    }
}
