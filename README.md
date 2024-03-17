## Easily manage your Laravel Cache with `CacheMachine`

[![Latest Version on Packagist](https://img.shields.io/packagist/v/joshembling/cache-machine.svg?style=flat-square)](https://packagist.org/packages/joshembling/cache-machine)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/joshembling/cache-machine/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/joshembling/cache-machine/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/joshembling/cache-machine/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/joshembling/cache-machine/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/joshembling/cache-machine.svg?style=flat-square)](https://packagist.org/packages/joshembling/cache-machine)

## About

`CacheMachine` is a simple, lightweight way of managing cache for your Laravel models. Instead of executing queries, you can 'withdraw' cached data using specific keys and simultaneously 'deposit' (save) new cache entries when needed.

One of the key advantages of CacheMachine is its automatic triggering feature during model save or delete actions. This ensures your cache is always up-to-date without requiring manual intervention. For those who prefer more control, you have the flexibility to force cache updates at your discretion. Once created, the cache persists indefinitely until your model undergoes an update.

CacheMachine is particularly well-suited for various use cases, including articles and blog posts, product and pricing information, select fields, translations, user profiles, images etc.

While CacheMachine is designed around caching model queries, it offers versatility. You can incorporate any data into your cache keys, ensuring they stay current with your model updates.

**Note**: Be mindful - if your models undergo frequent updates, such as every few seconds, it's advisable to configure your own caching methods according to your specific performance requirements.

## Compatability 

You are free to use any caching provider you want e.g. Redis, DynamoDB. Refer to [Laravel's documentation on caching](https://laravel.com/docs/10.x/cache#configuration) for further assistance.

This package is compatible with Laravel versions 10 and 11 and PHP versions >= 8.2.

## Installation

You can install the package via composer:

```bash
composer require joshembling/cache-machine
```

## Usage

To use CacheMachine, the same structure will apply to each of your models.

1) Add the `CacheMachine` trait.

```php
use JoshEmbling\CacheMachine\CacheMachine;

class Post extends Model
{
    use CacheMachine;

    // ...
}
```

2) Add the `cacheKeys()` method to your model. This must return an array with a structure of `string => callable`.

```php
use JoshEmbling\CacheMachine\CacheMachine;

class Post extends Model
{
    use CacheMachine;

    /**
     * @var array<string, callable>
     */
    public static function cacheKeys(): array
    {
        $keys = [
            // Cache all posts with the eloquent query as the callback
            'all_posts' => fn () => self::all(),
        ];

        return $keys;
    }

    // ...
}
```

3) You may prefer to dynamically refer to your keys as constants or properties within this class.

```php
use JoshEmbling\CacheMachine\CacheMachine;

class Post extends Model
{
    use CacheMachine;

    const ALL = 'all_posts';
    const SELECT = 'select_posts';

    /**
     * @var array<string, callable>
     */
    public static function cacheKeys(): array
    {
        $keys = [
             // Cache all posts
            self::ALL => fn () => self::all(),

             // Cache all posts in a key => value format
            self::SELECT => fn () => self::get()->mapWithKeys(
                fn ($type) => [
                    $type->id => $type->title,
                ]
            ),
        ];

        return $keys;
    }

    // ...
}
```

Once you have set up your model, you are able to `withdraw()` your cache. If it doesn't exist, CacheMachine will automatically `deposit` your cache for you when your callback function is valid. 

In other words, CacheMachine will fetch from the cache when it exists, or query the database if it doesn't.

```php
// You may pass a string to the withdraw method.
Post::withdraw('all_posts');

// Or one of your model's static properties, relating to a key defined in the `cacheKeys()` method.
Post::withdraw(Post::ALL);
```

If you would like to manually save to the cache e.g. you have manually added records to your database without triggering model observers, you may execute the following:
```php
Post::forceFetch(Post::ALL)
```

## Collections 

If your callback functions in your `cacheKeys()` return Eloquent queries, you can use any [collection method](https://laravel.com/docs/10.x/collections#available-methods) to filter your results. You will not need to query your database once the parent query is cached.

Here is a simple example of what you could do on a blog site:

```php
// Fetch all posts to display on an archive
Post::withdraw('all_posts');

// Display a single blog article
Post::withdraw('all_posts')
    ->firstWhere('id', 2);

// Filter a user search query
Post::withdraw('all_posts')
    ->where('title', 'like', '%Laravel%')
    ->orWhere('published_at', '>', now()->subDays(30))
    ->map(fn ($post) => strtoupper($post->title))
    ->sortDesc();
```

You may wish to enhance your collections even further so you are mitigating any heavy useage of your database. If you want additional features such as pagination, chunks etc. that don't come out of the box with Laravel, I recommend [laravel-collection-macros by Spatie](https://github.com/spatie/laravel-collection-macros/).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Josh Embling](https://github.com/joshembling)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
