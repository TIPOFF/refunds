<?php

declare(strict_types=1);

use Tipoff\Authorization\Permissions\BasePermissionsMigration;

class AddRefundPermissions extends BasePermissionsMigration
{
    public function up()
    {
        $permissions = [
            'view refunds' => ['Owner', 'Executive', 'Staff'],
            'request refunds' => ['Owner', 'Executive'],
            'issue refunds' => ['Owner', 'Executive']
        ];

        $this->createPermissions($permissions);
    }
}
