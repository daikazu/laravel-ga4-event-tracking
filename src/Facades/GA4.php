<?php

namespace Daikazu\GA4\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Daikazu\GA4\GA4
 */
class GA4 extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Daikazu\GA4\GA4::class;
    }
}
