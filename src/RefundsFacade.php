<?php

namespace Tipoff\Refunds;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tipoff\Refunds\Refunds
 */
class RefundsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'refunds';
    }
}
