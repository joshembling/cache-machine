<?php

namespace JoshEmbling\CacheMachine\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JoshEmbling\CacheMachine\CacheMachine
 */
class CacheMachine extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \JoshEmbling\CacheMachine\CacheMachine::class;
    }
}
