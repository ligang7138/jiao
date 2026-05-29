<?php

use App\Support\LegacyDoActionMap;
use App\Support\LegacyRouteMap;

return [
    'do_action_map' => LegacyDoActionMap::map(),
    'route_permission_map' => LegacyRouteMap::routeNameToPermission(),
];
