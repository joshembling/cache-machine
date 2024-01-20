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

    /**
     * Cache multiple items by generating and saving them to the cache.
     *
     * @param  array  $items  An associative array where keys represent cache keys and values are callbacks for cache generation.
     */
    public static function cacheMachine(array $items): void
    {
        foreach ($items as $key => $callback) {
            static::generateCache($key, $callback);
        }
    }

    /**
     * Retrieve an item from the cache, or force fetch and save if not found.
     *
     * @param  string  $key  The key to identify the cached item.
     * @return mixed The cached item or the result of the forced fetch.
     */
    public static function withdraw(string $key): mixed
    {
        return Cache::get($key) ?? static::forceFetch($key);
    }

    /**
     * Force fetch and save an item to the cache based on the provided key.
     *
     * @param  string  $key  The key to identify the cached item.
     * @return mixed The result of the force fetch and save operation.
     */
    public static function forceFetch(string $key): mixed
    {
        if (array_key_exists($key, static::cacheKeys())) {
            return static::forceSave($key, static::cacheKeys()[$key]);
        }
    }

    /**
     * Generate and save an item to the cache using the provided key and callback.
     *
     * @param  string  $key  The key to identify the cached item.
     * @param  callable  $callback  The callback function to generate the cache item.
     */
    private static function generateCache(string $key, callable $callback): void
    {
        static::modelSavingOrDeleting($key);
        static::modelSavedOrDeleted($key, $callback);
    }

    /**
     * Handle cache removal when the model is being saved or deleted.
     *
     * @param  string  $key  The key to identify the cached item.
     */
    private static function modelSavingOrDeleting(string $key): void
    {
        static::saving(function () use ($key) {
            Cache::forget($key);
        });

        static::deleting(function () use ($key) {
            Cache::forget($key);
        });
    }

    /**
     * Handle cache update when the model is saved or deleted.
     *
     * @param  string  $key  The key to identify the cached item.
     * @param  callable  $callback  The callback function to generate the cache item.
     */
    private static function modelSavedOrDeleted(string $key, callable $callback): void
    {
        static::saved(function () use ($key, $callback) {
            Cache::rememberForever($key, $callback);
        });

        static::deleted(function () use ($key, $callback) {
            Cache::rememberForever($key, $callback);
        });
    }

    /**
     * Force save an item to the cache if not already present.
     *
     * @param  string  $key  The key to identify the cached item.
     * @param  callable  $callback  The callback function to generate the cache item.
     * @return mixed The result of the force save operation.
     */
    private static function forceSave(string $key, callable $callback): mixed
    {
        if (
            is_null(Cache::get($key)) &&
            is_callable($callback)
        ) {
            Cache::rememberForever($key, $callback);

            return static::cacheKeys()[$key]();
        }
    }
}
