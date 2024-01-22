<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use JoshEmbling\CacheMachine\Tests\Models\Category;
use JoshEmbling\CacheMachine\Tests\Models\Post;
use Mockery;

it('can create posts', function () {
    createPost();

    expect(Post::count())->toBe(1);
});

test('cache is updated when model is created', function () {
    Cache::shouldReceive('forget')
        ->once()
        ->with(Post::ALL)
        ->andReturn(null);

    Cache::shouldReceive('rememberForever')
        ->once()
        ->with(Post::ALL, Mockery::on(function ($closure) {
            if (! ($closure instanceof Closure)) {
                return false;
            }

            $result = $closure()->toArray();

            expect($result[0])->toEqual([
                'id' => 1,
                'category_id' => null,
                'title' => 'Title',
                'content' => 'This is the content',
                'published_at' => '2024-01-01T00:00:00.000000Z',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ]);

            return true;
        }));

    createPost();
});

test('cache is updated when model is updated', function () {
    createPost();

    Cache::shouldReceive('forget')
        ->once()
        ->with(Post::ALL)
        ->andReturn(null);

    Cache::shouldReceive('rememberForever')
        ->once()
        ->with(Post::ALL, Mockery::on(function ($closure) {
            if (! ($closure instanceof Closure)) {
                return false;
            }

            $result = $closure()->toArray();

            expect($result[0])->toEqual([
                'id' => 1,
                'category_id' => null,
                'title' => 'Title',
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
    createPost([
        'title' => 'My title 1',
        'content' => 'Content 1',
        'published_at' => now(),
    ]);
    createPost([
        'title' => 'My title 2',
        'content' => 'Content 2',
        'published_at' => now(),
    ]);
    createPost([
        'title' => 'My title 3',
        'content' => 'Content 3',
        'published_at' => now(),
    ]);

    expect(Post::count())->toBe(3);

    Cache::shouldReceive('forget')
        ->once()
        ->with(Post::ALL)
        ->andReturn(null);

    Cache::shouldReceive('rememberForever')
        ->once()
        ->with(Post::ALL, Mockery::on(function ($closure) {
            if (! ($closure instanceof Closure)) {
                return false;
            }

            $result = $closure()->toArray();

            expect($result)->toEqual([
                [
                    'id' => 2,
                    'category_id' => null,
                    'title' => 'My title 2',
                    'content' => 'Content 2',
                    'published_at' => '2024-01-01T00:00:00.000000Z',
                    'created_at' => '2024-01-01T00:00:00.000000Z',
                    'updated_at' => '2024-01-01T00:00:00.000000Z',
                ],
                [
                    'id' => 3,
                    'category_id' => null,
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
        ->with(Post::ALL, Mockery::on(function ($closure) {

            if (! ($closure instanceof Closure)) {
                return false;
            }

            $result = $closure()->toArray();

            expect($result[0])->toEqual([
                'id' => 1,
                'category_id' => null,
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

    Post::forceFetch(Post::ALL);

    //$this->cacheRepositorySpy->shouldHaveReceived('get');
});

test('the cache withdraw method always returns a result and saves cache', function () {
    expect(Post::count())->toBe(0);

    createPost();

    expect(Post::count())->toBe(1);

    Cache::flush();

    expect(Cache::get(Post::ALL))->toBe(null);

    Cache::shouldReceive('rememberForever')
        ->once()
        ->with(Post::ALL, Mockery::on(function ($closure) {

            if (! ($closure instanceof Closure)) {
                return false;
            }

            $result = $closure()->toArray();

            expect($result[0])->toEqual([
                'id' => 1,
                'category_id' => null,
                'title' => 'Title',
                'content' => 'This is the content',
                'published_at' => '2024-01-01T00:00:00.000000Z',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ]);

            return true;
        }));

    Post::withdraw(Post::ALL);
});

it('can save cache with an eloquent relationship', function () {
    Cache::shouldReceive('rememberForever')
        ->once()
        ->with(Post::ALL, Mockery::on(function ($closure) {

            if (! ($closure instanceof Closure)) {
                return false;
            }

            $result = $closure()->toArray();

            expect($result[0])->toEqual([
                'id' => 1,
                'category_id' => null,
                'title' => 'Title',
                'content' => 'This is the content',
                'published_at' => '2024-01-01T00:00:00.000000Z',
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'updated_at' => '2024-01-01T00:00:00.000000Z',
            ]);

            return true;
        }));

    createPost([
        'category_id' => Category::create(['name' => 'Category'])->id,
        'title' => 'Title',
        'content' => 'This is the content',
        'published_at' => now(),
    ]);
});

function createPost(?array $attributes = null): void
{
    Post::create([
        'category_id' => $attributes['category_id'] ?? null,
        'title' => $attributes['title'] ?? 'Title',
        'content' => $attributes['content'] ?? 'This is the content',
        'published_at' => $attributes['published_at'] ?? now(),
    ]);
}
