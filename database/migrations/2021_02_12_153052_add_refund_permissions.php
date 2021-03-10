<?php

declare(strict_types=1);

use Tipoff\Authorization\Permissions\BasePermissionsMigration;

class AddRefundPermissions extends BasePermissionsMigration
{
    public function up()
    {
        $permissions = [
            'view refunds' => ['Owner', 'Staff'],
            'request refunds' => ['Owner'],
            'issue refunds' => ['Owner']
        ];

        $this->createPermissions($permissions);
    }
}
