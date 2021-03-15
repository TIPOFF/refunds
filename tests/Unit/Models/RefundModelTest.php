<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Tests\Unit\Models;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Refunds\Models\Refund;
use Tipoff\Refunds\Tests\TestCase;

class RefundModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function create()
    {
        $model = Refund::factory()->create();
        $this->assertNotNull($model);
    }
}
