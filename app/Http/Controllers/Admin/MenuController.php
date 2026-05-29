<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Support\LegacyMenuBuilder;
use Illuminate\Support\Facades\Schema;

class MenuController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!Schema::hasTable('system_menu')) {
            return ResponseHelper::success([], 'Success');
        }

        $rows = LegacyMenuBuilder::buildForUser($user);

        return ResponseHelper::success($rows, 'Success');
    }
}
