<?php

namespace Mha\LaravelModelWatch\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mha\LaravelModelWatch\LaravelModelWatch
 */
class LaravelModelWatch extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mha\LaravelModelWatch\LaravelModelWatch::class;
    }
}
