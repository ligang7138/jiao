<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DictionaryController extends Controller
{
    public function index(Request $request)
    {
        $keys = array_filter(explode(',', (string) $request->query('keys', '')));

        $dictionary = [
            'goods_status' => [
                ['value' => 0, 'label' => '下架'],
                ['value' => 1, 'label' => '上架'],
            ],
            'order_status' => [
                ['value' => 10, 'label' => '待审核'],
                ['value' => 20, 'label' => '待配送'],
                ['value' => 30, 'label' => '配送中'],
                ['value' => 40, 'label' => '已签收'],
                ['value' => 50, 'label' => '已完成'],
            ],
        ];

        if ($keys) {
            $dictionary = array_intersect_key($dictionary, array_flip($keys));
        }

        return ResponseHelper::success($dictionary, 'Success');
    }
}
