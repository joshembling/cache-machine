# `Deposit` and `Withdraw` with `CacheMachine`

[![Latest Version on Packagist](https://img.shields.io/packagist/v/joshembling/cache-machine.svg?style=flat-square)](https://packagist.org/packages/joshembling/cache-machine)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/joshembling/cache-machine/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/joshembling/cache-machine/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/joshembling/cache-machine/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/joshembling/cache-machine/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/joshembling/cache-machine.svg?style=flat-square)](https://packagist.org/packages/joshembling/cache-machine)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## About

`CacheMachine` is a simple, lightweight and efficient way of managing cache for your Laravel models. You 'withdraw' your cache with a key (instead of running a query), and at the same time, make 'deposits' (save cache if it doesn't already exist).

CacheMachine is triggered whenever the save or delete actions initiate on your models, meaning your cache will automatically update when something has changed. If you want more control, you are not limited to this functionality - you may also force cache updates whenever you want.

The main benefit of CacheMachine means you don't have to worry about out-of-date cache. It will exist (forever) until your model updates.

CacheMachine can be a great use case for articles and blog posts, products and pricing data, select fields, translations, user profiles, images etc. 

Although this package is based around caching your model queries, you are not limited to this. You may pass any data you want to your cache keys, and they will still update when your model updates.

**Note**: Be mindful - if you have models that update constantly e.g. every few seconds, you should likely configure your own caching methods tailored to the way you want them to perform.

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

3) Add the `cacheKeys()` method to your model. This method must return an array with an array structure of `string => callable`.

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

Once you have set up your model, you are able to `withdraw()` your cache. If it doesn't exist, CacheMachine will automatically `deposit` your cache for you if your callback function is valid. 

In other words, CacheMachine will fetch from the cache where it exists, or query the database if it doesn't, whilst caching it ready for the next use.

```php
// You may pass a string to the withdraw method.
Post::withdraw('all_posts');

// Or one of your model's static properties, relating to a key defined in the `cacheKeys()` method.
Post::withdraw(Post::$all);
```

If you would like to force saving to the cache e.g. you have manually added records to your database without triggering model observers, you may execute the following:
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
