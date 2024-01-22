<?php

namespace JoshEmbling\CacheMachine\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JoshEmbling\CacheMachine\CacheMachine;

class Post extends Model
{
    use CacheMachine, HasFactory;

    const ALL = 'all_posts';

    const SELECT = 'select_posts';

    protected $fillable = [
        'title',
        'content',
        'published_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'published_at' => 'datetime',
    ];

    /**
     * @var array<string, callable>
     */
    public static function cacheKeys(): array
    {
        $keys = [
            self::ALL => fn () => self::get(),
            self::SELECT => fn () => self::get()->mapWithKeys(fn ($type) => [$type->id => $type->title]),
        ];

        return $keys;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
