<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ErrorCode;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\School\SchoolService;
use Illuminate\Http\Request;

/**
 * 学校管理控制器
 */
class SchoolController extends Controller
{
    public function __construct(
        protected SchoolService $schoolService
    ) {}

    public function index(Request $request)
    {
        $params = $request->only([
            'keyword',
            'school_name',
            'school_sn',
            'school_district',
            'status',
            'bidding_status',
            'page',
            'page_size',
        ]);

        return ResponseHelper::success($this->schoolService->getList($params));
    }

    public function options()
    {
        return ResponseHelper::success([
            'districts' => $this->schoolService->getDistricts(),
            'school_periods' => $this->schoolService->getSchoolPeriods(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $school = $this->schoolService->create($request->only([
                'school_name',
                'school_district',
                'school_period',
                'status',
            ]));

            return ResponseHelper::success(['id' => $school->id], '添加成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(ErrorCode::VALIDATION_ERROR, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '添加失败: ' . $e->getMessage());
        }
    }

    public function show(int $id)
    {
        try {
            return ResponseHelper::success($this->schoolService->getDetail($id));
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40004, $e->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $school = $this->schoolService->update($id, $request->only([
                'school_name',
                'school_district',
                'school_period',
                'status',
            ]));

            return ResponseHelper::success(['id' => $school->id], '修改成功');
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
            $school = $this->schoolService->setStatus($id, $status);

            return ResponseHelper::success(['id' => $school->id, 'status' => $school->status], '设置成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '设置失败');
        }
    }

    public function getActiveSchools()
    {
        return ResponseHelper::success($this->schoolService->getActiveSchools());
    }
}
