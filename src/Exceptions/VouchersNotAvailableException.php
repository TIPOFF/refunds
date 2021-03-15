<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Exceptions;

use Throwable;

class VouchersNotAvailableException extends \InvalidArgumentException implements RefundException
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct('Voucher services are not enabled.', $code, $previous);
    }
}
