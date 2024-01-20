<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use JoshEmbling\CacheMachine\Tests\Models\Post;
use Mockery;

it('can create posts', function () {
    Post::create([
        'title' => fake()->sentence(4),
        'content' => fake()->paragraphs(3, true),
        'published_at' => fake()->dateTime(),
    ]);

    expect(Post::count())->toBe(1);
});

test('cache is updated when model is created', function () {
    Cache::shouldReceive('forget')
        ->once()
        ->with(Post::$all)
        ->andReturn(null);

    Cache::shouldReceive('rememberForever')
        ->once()
        ->with(Post::$all, Mockery::on(function ($closure) {
            if (! ($closure instanceof Closure)) {
                return false;
            }

            $result = $closure()->toArray();

            expect($result[0])->toEqual([
                'id' => 1,
                'title' => 'My title',
                'content' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit! Eveniet modi beatae accusantium maxime sequi vitae doloribus, quidem distinctio ea animi!',
                'published_at' => '2024-01-01T00:00:00.000000Z',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ]);

            return true;
        }));

    Post::create([
        'title' => 'My title',
        'content' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit! Eveniet modi beatae accusantium maxime sequi vitae doloribus, quidem distinctio ea animi!',
        'published_at' => now(),
    ]);
});

test('cache is updated when model is updated', function () {
    Post::create([
        'title' => 'My title',
        'content' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit! Eveniet modi beatae accusantium maxime sequi vitae doloribus, quidem distinctio ea animi!',
        'published_at' => now(),
    ]);

    Cache::shouldReceive('forget')
        ->once()
        ->with(Post::$all)
        ->andReturn(null);

    Cache::shouldReceive('rememberForever')
        ->once()
        ->with(Post::$all, Mockery::on(function ($closure) {
            if (! ($closure instanceof Closure)) {
                return false;
            }

            $result = $closure()->toArray();

            expect($result[0])->toEqual([
                'id' => 1,
                'title' => 'My title',
                'content' => 'Updated',
                'published_at' => '2024-01-01T00:00:00.000000Z',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ]);

            return true;
        }));

    Post::first()->update([
        'content' => 'Updated',
    ]);
});

test('cache is updated when model is deleted', function () {
    Post::create([
        'title' => 'My title 1',
        'content' => 'Content 1',
        'published_at' => now(),
    ]);
    Post::create([
        'title' => 'My title 2',
        'content' => 'Content 2',
        'published_at' => now(),
    ]);
    Post::create([
        'title' => 'My title 3',
        'content' => 'Content 3',
        'published_at' => now(),
    ]);

    expect(Post::count())->toBe(3);

    Cache::shouldReceive('forget')
        ->once()
        ->with(Post::$all)
        ->andReturn(null);

    Cache::shouldReceive('rememberForever')
        ->once()
        ->with(Post::$all, Mockery::on(function ($closure) {
            if (! ($closure instanceof Closure)) {
                return false;
            }

            $result = $closure()->toArray();

            expect($result)->toEqual([
                [
                    'id' => 2,
                    'title' => 'My title 2',
                    'content' => 'Content 2',
                    'published_at' => '2024-01-01T00:00:00.000000Z',
                    'created_at' => '2024-01-01T00:00:00.000000Z',
                    'updated_at' => '2024-01-01T00:00:00.000000Z',
                ],
                [
                    'id' => 3,
                    'title' => 'My title 3',
                    'content' => 'Content 3',
                    'published_at' => '2024-01-01T00:00:00.000000Z',
                    'created_at' => '2024-01-01T00:00:00.000000Z',
                    'updated_at' => '2024-01-01T00:00:00.000000Z',
                ],
            ]);

            return true;
        }));

    Post::first()->delete();

    expect(Post::count())->toBe(2);
});

test('cache is updated when forced and not via a model change observer', function () {
    Cache::shouldReceive('rememberForever')
        ->once()
        ->with(Post::$all, Mockery::on(function ($closure) {

            if (! ($closure instanceof Closure)) {
                return false;
            }

            $result = $closure()->toArray();

            expect($result[0])->toEqual([
                'id' => 1,
                'title' => 'My title',
                'content' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit! Eveniet modi beatae accusantium maxime sequi vitae doloribus, quidem distinctio ea animi!',
                'published_at' => '2024-01-01T00:00:00.000000Z',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ]);

            return true;
        }));

    // Create record silently to not trigger model observers
    DB::table('posts')->insert([
        'title' => 'My title',
        'content' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit! Eveniet modi beatae accusantium maxime sequi vitae doloribus, quidem distinctio ea animi!',
        'published_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Post::forceFetch(Post::$all);

    //$this->cacheRepositorySpy->shouldHaveReceived('get');
});

test('the cache withdraw method always returns a result and saves cache', function () {
    expect(Post::count())->toBe(0);

    Post::create([
        'title' => 'Title',
        'content' => 'Content',
        'published_at' => now(),
    ]);

    expect(Post::count())->toBe(1);

    Cache::flush();

    expect(Cache::get(Post::$all))->toBe(null);

    Cache::shouldReceive('rememberForever')
        ->once()
        ->with(Post::$all, Mockery::on(function ($closure) {

            if (! ($closure instanceof Closure)) {
                return false;
            }

            $result = $closure()->toArray();

            expect($result[0])->toEqual([
                'id' => 1,
                'title' => 'Title',
                'content' => 'Content',
                'published_at' => '2024-01-01T00:00:00.000000Z',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ]);

            return true;
        }));

    Post::withdraw(Post::$all);
});
