<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Tests\Feature\Nova;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Refunds\Models\Refund;
use Tipoff\Refunds\Tests\TestCase;

class RefundResourceTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function index()
    {
        Refund::factory()->count(4)->create();

        $this->actingAs(self::createPermissionedUser('view refunds', true));

        $response = $this->getJson('nova-api/refunds')
            ->assertOk();

        $this->assertCount(4, $response->json('resources'));
    }

    /** @test */
    public function show()
    {
        $refund = Refund::factory()->create();

        $this->actingAs(self::createPermissionedUser('view refunds', true));

        $response = $this->getJson("nova-api/refunds/{$refund->id}")
            ->assertOk();

        $this->assertEquals($refund->id, $response->json('resource.id.value'));
    }
}
