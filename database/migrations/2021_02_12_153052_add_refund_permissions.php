<?php

declare(strict_types=1);

use Tipoff\Authorization\Permissions\BasePermissionsMigration;

class AddRefundPermissions extends BasePermissionsMigration
{
    public function up()
    {
        $permissions = [
            'view refunds',
            'request refunds',
            'issue refunds'
        ];

        $this->createPermissions($permissions);
    }
}
