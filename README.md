# `Deposit` and `Withdraw` with `CacheMachine`

[![Latest Version on Packagist](https://img.shields.io/packagist/v/joshembling/cache-machine.svg?style=flat-square)](https://packagist.org/packages/joshembling/cache-machine)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/joshembling/cache-machine/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/joshembling/cache-machine/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/joshembling/cache-machine/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/joshembling/cache-machine/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/joshembling/cache-machine.svg?style=flat-square)](https://packagist.org/packages/joshembling/cache-machine)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## About

`CacheMachine` is a simple, lightweight and efficient way of managing cache for your Laravel models. Instead of executing queries, you can 'withdraw' cached data using specific keys and simultaneously 'deposit' (save) new cache entries when needed.

One of the key advantages of CacheMachine is its automatic triggering mechanism during model save or delete actions. This ensures that your cache is always up-to-date without requiring manual intervention. For those who prefer more control, you have the flexibility to force cache updates at your discretion.

The main benefit of CacheMachine means you don't have to worry about out-of-date cache. Once created, the cache persists indefinitely until your model undergoes an update.

CacheMachine is particularly well-suited for various use cases, including articles and blog posts, product and pricing information, select fields, translations, user profiles, images etc.

While CacheMachine is designed around caching model queries, it offers versatility. You can incorporate any data you desire into your cache keys, ensuring they stay current with your model updates.

**Note**: Be mindful - if your models undergo frequent updates, such as every few seconds, it's advisable to configure your caching methods according to your specific performance requirements. This is outside the scope of this package.

You are free to use any caching provider you want e.g. Redis, DynamoDB. Refer to [Laravel's documentation on caching](https://laravel.com/docs/10.x/cache#configuration) for further assistance.

## Installation

You can install the package via composer:

```bash
composer require joshembling/cache-machine
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="cache-machine-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

To use CacheMachine, the same structure will apply to each of your models.

1) Add the `CacheMachine` trait.

```php
class Post extends Model
{
    use CacheMachine;

    // ...
}
```

2) Add the `boot()` method to your model. Within this method, add the `deposit` static method with an argument of `cacheKeys()`.

```php
class Post extends Model
{
    use CacheMachine;

    protected static function boot()
    {
        parent::boot();

        self::deposit(self::cacheKeys()); 
    }
    
    // ...
}
```

3) Add the `cacheKeys()` method to your model. This must return an array with a structure of `string => callable`.

```php
class Post extends Model
{
    use CacheMachine;

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
            // Cache all posts
            'all_posts' => fn () => self::all(),
        ];

        return $keys;
    }

    // ...
}
```

4) You may prefer to dynamically refer to your keys as properties within this class.

```php
class Post extends Model
{
    use CacheMachine;

    public static string $all = 'all_posts';
    public static string $select = 'select_posts';

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
             // Cache all posts
            self::$all => fn () => self::get(),

             // Cache all posts in a key => value style, like you may see on a select dropdown
            self::$select => fn () => self::get()->mapWithKeys(fn ($type) => [$type->id => $type->title]),
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
Post::withdraw(Post::$all);
```

If you would like to manually save to the cache e.g. you have manually added records to your database without triggering model observers, you may execute the following:
```php
Post::forceFetch(Post::$all)
```

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
