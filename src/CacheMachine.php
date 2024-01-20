<?php

namespace JoshEmbling\CacheMachine;

use Illuminate\Support\Facades\Cache;

trait CacheMachine
{
    abstract protected static function boot();

    /**
     * @var array<string, callable> $keys
     */
    abstract public static function cacheKeys(): array;

    public static function cacheMachine(array $items)
    {
        foreach ($items as $key => $callback) {
            static::generateCache($key, $callback);
        }
    }

    public static function withdraw(string $key)
    {
        return Cache::get($key) ?? static::forceFetch($key);
    }

    public static function generateCache(string $key, callable $callback): void
    {
        static::modelSavingOrDeleting($key);
        static::modelSavedOrDeleted($key, $callback);
    }

    public static function forceFetch(string $key): mixed
    {
        if (array_key_exists($key, self::cacheKeys())) {
            return static::forceSave($key, self::cacheKeys()[$key]);
        }
    }

    private static function modelSavingOrDeleting(string $key): void
    {
        static::saving(function () use ($key) {
            Cache::forget($key);
        });

        static::deleting(function () use ($key) {
            Cache::forget($key);
        });
    }

    private static function modelSavedOrDeleted(string $key, callable $callback): void
    {
        static::saved(function () use ($key, $callback) {
            Cache::rememberForever($key, $callback);
        });

        static::deleted(function () use ($key, $callback) {
            Cache::rememberForever($key, $callback);
        });
    }

    private static function forceSave(string $key, callable $callback)
    {
        if (
            is_null(Cache::get($key)) &&
            is_callable($callback)
        ) {
            Cache::rememberForever($key, $callback);

            return self::cacheKeys()[$key]();
        }
    }
}
