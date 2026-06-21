<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reaction extends Model
{
    use HasFactory;

    public const OPTIONS = [
        'heart' => ['symbol' => '❤️', 'label' => 'Нравится'],
        'fire' => ['symbol' => '🔥', 'label' => 'Сильно'],
        'clap' => ['symbol' => '👏', 'label' => 'Поддерживаю'],
        'eyes' => ['symbol' => '👀', 'label' => 'Смотрю'],
    ];

    protected $fillable = [
        'user_id',
        'kind',
    ];

    public static function options(): array
    {
        return self::OPTIONS;
    }

    public static function kinds(): array
    {
        return array_keys(self::OPTIONS);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }
}
