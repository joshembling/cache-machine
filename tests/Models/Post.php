<?php

namespace JoshEmbling\CacheMachine\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JoshEmbling\CacheMachine\CacheMachine;

class Post extends Model
{
    use CacheMachine, HasFactory;

    public static string $all = 'all_posts';

    public static string $select = 'select_posts';

    protected $fillable = [
        'title',
        'content',
        'published_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        self::deposit(self::cacheKeys());
    }

    /**
     * @var array<string, callable>
     */
    public static function cacheKeys(): array
    {
        $keys = [
            Post::$all => fn () => Post::get(),
            Post::$select => fn () => Post::get()->mapWithKeys(fn ($type) => [$type->id => $type->title]),
        ];

        return $keys;
    }
}
