<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Tests\Unit\Migrations;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tipoff\Refunds\Tests\TestCase;

class PermissionsMigrationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function permissions_seeded()
    {
        $this->assertTrue(Schema::hasTable('permissions'));

        $seededPermissions = app(Permission::class)->whereIn('name', [
            'view refunds',
            'request refunds',
            'issue refunds',
        ])->pluck('name');

        $this->assertCount(3, $seededPermissions);
    }
}
