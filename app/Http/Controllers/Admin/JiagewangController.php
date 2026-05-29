<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Jiagewang\ImportRequest;
use App\Http\Requests\Admin\Jiagewang\UpdatePriceRequest;
use App\Services\Jiagewang\JiagewangService;
use App\Helpers\ResponseHelper;
use App\Constants\ErrorCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 价格网管理控制器
 */
class JiagewangController extends Controller
{
    protected JiagewangService $jiagewangService;

    public function __construct(JiagewangService $jiagewangService)
    {
        $this->jiagewangService = $jiagewangService;
    }

    /**
     * 指导价列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $params = $request->only([
            'goods_sn',
            'goods_name',
            'cate_id',
            'scate_id',
            'page',
            'page_size',
        ]);

        $result = $this->jiagewangService->getList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 导入指导价
     *
     * @param ImportRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(ImportRequest $request)
    {
        $file = $request->file('file');
        $user = Auth::user();

        try {
            $result = $this->jiagewangService->import(
                $file,
                $user->id,
                $user->name ?? $user->username
            );

            $message = "导入完成：成功 {$result['success_count']} 条";
            if ($result['error_count'] > 0) {
                $message .= "，失败 {$result['error_count']} 条";
            }

            return ResponseHelper::success([
                'success_count' => $result['success_count'],
                'error_count' => $result['error_count'],
                'error_list' => $result['error_list'],
            ], $message);
        } catch (\Exception $e) {
            return ResponseHelper::error(
                ErrorCode::FILE_ERROR,
                '导入失败: ' . $e->getMessage()
            );
        }
    }

    /**
     * 编辑指导价
     *
     * @param UpdatePriceRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePriceRequest $request)
    {
        $data = $request->validated();
        $user = Auth::user();

        try {
            $this->jiagewangService->updatePrice(
                $data['id'] ?? 0,
                $data['goods_sn'],
                $data['goods_id'],
                $data['price'],
                $user->name ?? $user->username
            );

            return ResponseHelper::success([], '指导价更新成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(
                ErrorCode::DATABASE_ERROR,
                '指导价更新失败: ' . $e->getMessage()
            );
        }
    }

    /**
     * 历史记录列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $params = $request->only([
            'goods_sn',
            'goods_name',
            'cate_name',
            'scate_name',
            'start_date',
            'end_date',
            'page',
            'page_size',
        ]);

        $result = $this->jiagewangService->getHistoryList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 商品匹配列表（已匹配指导价）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function match(Request $request)
    {
        $params = $request->only([
            'goods_sn',
            'goods_name',
            'cate_name',
            'scate_name',
            'page',
            'page_size',
        ]);

        $result = $this->jiagewangService->getMatchList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 未匹配商品列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function noMatch(Request $request)
    {
        $params = $request->only([
            'goods_sn',
            'goods_name',
            'cate_id',
            'scate_id',
            'page',
            'page_size',
        ]);

        $result = $this->jiagewangService->getNoMatchList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 导出指导价
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {
        $params = $request->only([
            'goods_sn',
            'goods_name',
            'cate_id',
            'scate_id',
        ]);

        try {
            $data = $this->jiagewangService->export($params);

            return ResponseHelper::success([
                'list' => $data,
                'total' => count($data),
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error(
                ErrorCode::FILE_ERROR,
                '导出失败: ' . $e->getMessage()
            );
        }
    }

    /**
     * 获取导入错误列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importErrors(Request $request)
    {
        $errorKey = $request->input('error_key');

        if (empty($errorKey)) {
            return ResponseHelper::error(ErrorCode::VALIDATION_ERROR, '缺少错误标识');
        }

        $errorList = $this->jiagewangService->getImportErrorList($errorKey);

        if ($errorList === null) {
            return ResponseHelper::error(ErrorCode::NOT_FOUND, '错误数据已过期或不存在');
        }

        return ResponseHelper::success([
            'error_list' => $errorList,
        ]);
    }
}
