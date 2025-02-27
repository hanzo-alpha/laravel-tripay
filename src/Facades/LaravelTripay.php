<?php

namespace HanzoAlpha\LaravelTripay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \HanzoAlpha\LaravelTripay\LaravelTripay
 */
class LaravelTripay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \HanzoAlpha\LaravelTripay\LaravelTripay::class;
    }
}
