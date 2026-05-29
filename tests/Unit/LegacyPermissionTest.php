<?php

namespace Tests\Unit;

use App\Support\LegacyPermission;
use Tests\TestCase;

class LegacyPermissionTest extends TestCase
{
    public function test_goods_show_accepts_index_or_edit_permission(): void
    {
        $permission = LegacyPermission::resolveByRouteName('admin.goods.show');
        $this->assertSame(['goods.index', 'goods.edit'], $permission);

        $userWithIndex = (object) ['privilege' => ['goods.index']];
        $userWithEdit = (object) ['privilege' => ['goods.edit']];
        $userWithout = (object) ['privilege' => ['order.index']];

        $this->assertTrue(LegacyPermission::userHasPermission($userWithIndex, $permission));
        $this->assertTrue(LegacyPermission::userHasPermission($userWithEdit, $permission));
        $this->assertFalse(LegacyPermission::userHasPermission($userWithout, $permission));
    }

    public function test_unmapped_route_name_returns_null_and_passes(): void
    {
        $this->assertNull(LegacyPermission::resolveByRouteName('admin.goods.publish'));
        $this->assertTrue(LegacyPermission::userHasPermission((object) ['privilege' => []], null));
    }

    public function test_wildcard_permission_matches_module_actions(): void
    {
        $user = (object) ['privilege' => ['goods.*']];

        $this->assertTrue(LegacyPermission::userHasPermission($user, 'goods.index'));
        $this->assertTrue(LegacyPermission::userHasPermission($user, 'goods.edit'));
    }
}
