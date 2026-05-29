<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ErrorCode;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\School\CanteenService;
use Illuminate\Http\Request;

/**
 * 食堂管理控制器
 */
class CanteenController extends Controller
{
    public function __construct(
        protected CanteenService $canteenService
    ) {}

    public function index(Request $request, int $schoolId = 0)
    {
        $params = $request->only([
            'keyword',
            'name',
            'canteen_sn',
            'school_name',
            'school_sn',
            'school_district',
            'emergency_linkman',
            'emergency_mobile',
            'receive_linkman',
            'receive_mobile',
            'canteen_type',
            'status',
            'group_id',
            'school_id',
            'page',
            'page_size',
        ]);

        $routeSchoolId = (int) ($request->route('schoolId') ?? 0);
        if ($routeSchoolId > 0) {
            $params['school_id'] = $routeSchoolId;
        } elseif ($schoolId > 0) {
            $params['school_id'] = $schoolId;
        }

        return ResponseHelper::success($this->canteenService->getList($params));
    }

    public function active(Request $request)
    {
        return ResponseHelper::success($this->canteenService->getActiveCanteens([
            'exclude_grouped' => $request->boolean('exclude_grouped'),
        ]));
    }

    public function store(Request $request, int $schoolId = 0)
    {
        $data = $request->all();
        $routeSchoolId = (int) ($request->route('schoolId') ?? 0);
        if ($routeSchoolId > 0) {
            $data['school_id'] = $routeSchoolId;
        } elseif ($schoolId > 0) {
            $data['school_id'] = $schoolId;
        }

        try {
            $canteen = $this->canteenService->create($data);

            return ResponseHelper::success(['id' => $canteen->id], '添加成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(ErrorCode::VALIDATION_ERROR, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '添加失败');
        }
    }

    public function showById(int $id)
    {
        try {
            return ResponseHelper::success($this->canteenService->getDetail($id));
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40004, $e->getMessage());
        }
    }

    public function updateById(Request $request, int $id)
    {
        try {
            $canteen = $this->canteenService->update($id, $request->all());

            return ResponseHelper::success(['id' => $canteen->id], '修改成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40004, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '修改失败:' . $e->getMessage());
        }
    }

    public function changeStatus(Request $request, int $id)
    {
        try {
            $status = (int) $request->input('status');
            $canteen = $this->canteenService->setStatus($id, $status);

            return ResponseHelper::success([
                'id' => $canteen->id,
                'status' => $canteen->status,
            ], '设置成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '设置失败');
        }
    }
}
