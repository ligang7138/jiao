<?php

namespace Tests\Unit;

use App\Support\LegacyDoActionMap;
use App\Support\LegacyRouteMap;
use Tests\TestCase;

class LegacyContractMapTest extends TestCase
{
    public function test_route_name_permission_map_not_empty(): void
    {
        $map = LegacyRouteMap::routeNameToPermission();
        $this->assertNotEmpty($map);
        $this->assertArrayHasKey('admin.goods.index', $map);
        $this->assertSame('goods.index', $map['admin.goods.index']);
    }

    public function test_do_action_map_has_core_goods_contract(): void
    {
        $map = LegacyDoActionMap::map();
        $this->assertArrayHasKey('goods.index', $map);
        $this->assertSame('GET', $map['goods.index']['method']);
        $this->assertSame('/api/v1/admin/goods', $map['goods.index']['uri']);
    }
}
