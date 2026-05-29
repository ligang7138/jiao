<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Finance\ReceivableService;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Admin\Finance\ReceiptStoreRequest;
use App\Http\Requests\Admin\Finance\AccountStoreRequest;
use Illuminate\Http\Request;
use App\Constants\ErrorCode;

/**
 * 应收账款管理控制器
 */
class ReceivableController extends Controller
{
    protected ReceivableService $receivableService;

    public function __construct(ReceivableService $receivableService)
    {
        $this->receivableService = $receivableService;
    }

    /**
     * 已收货订单列表（用于生成对账单）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $params = $request->only([
            'keyword',
            'school_id',
            'supplier_id',
            'start_date',
            'end_date',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->receivableService->getOrderList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 对账单列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiptList(Request $request)
    {
        $params = $request->only([
            'keyword',
            'canteen_id',
            'supplier_id',
            'invoice_status',
            'bill_status',
            'school_confirm_status',
            'start_date',
            'end_date',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->receivableService->getReceiptList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 对账单详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiptDetail(int $id)
    {
        try {
            $detail = $this->receivableService->getReceiptDetail($id);

            return ResponseHelper::success($detail);
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::NOT_FOUND, '对账单不存在');
        }
    }

    /**
     * 创建对账单
     *
     * @param ReceiptStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiptStore(ReceiptStoreRequest $request)
    {
        $data = $request->validated();

        try {
            $orderIds = $data['order_ids'] ?? [];
            unset($data['order_ids']);

            $receipt = $this->receivableService->createReceipt($data, $orderIds);

            return ResponseHelper::success([
                'id' => $receipt->id,
                'voucher_sn' => $receipt->voucher_sn,
            ], '对账单创建成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::INTERNAL_ERROR, '对账单创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 调整对账单
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiptAdjust(Request $request, int $id)
    {
        $data = $request->validate([
            'remark' => 'nullable|string|max:500',
            'school_confirm_status' => 'nullable|integer|in:0,1,2',
        ]);

        try {
            $receipt = $this->receivableService->adjustReceipt($id, $data);

            return ResponseHelper::success([
                'id' => $receipt->id,
            ], '对账单调整成功');
        } catch (\Exception $e) {
            $code = $e->getCode() ?: ErrorCode::INTERNAL_ERROR;
            return ResponseHelper::error($code, '对账单调整失败: ' . $e->getMessage());
        }
    }

    /**
     * 账单明细列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountList(Request $request)
    {
        $params = $request->only([
            'receipt_id',
            'type',
            'status',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->receivableService->getAccountList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 新增账单明细
     *
     * @param AccountStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountStore(AccountStoreRequest $request)
    {
        $data = $request->validated();

        try {
            $account = $this->receivableService->addAccount($data);

            return ResponseHelper::success([
                'id' => $account->id,
            ], '账单明细添加成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::INTERNAL_ERROR, '账单明细添加失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除账单明细
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountDestroy(int $id)
    {
        try {
            $this->receivableService->deleteAccount($id);

            return ResponseHelper::success([], '账单明细删除成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::INTERNAL_ERROR, '账单明细删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 开票
     *
     * @param Request $request
     * @param int $receiptId
     * @return \Illuminate\Http\JsonResponse
     */
    public function invoice(Request $request, int $receiptId)
    {
        $data = $request->validate([
            'price' => 'required|numeric|min:0.01',
            'remark' => 'nullable|string|max:500',
        ]);

        try {
            $account = $this->receivableService->invoice(
                $receiptId,
                $data['price'],
                $data['remark'] ?? ''
            );

            return ResponseHelper::success([
                'id' => $account->id,
            ], '开票成功');
        } catch (\Exception $e) {
            $code = $e->getCode() ?: ErrorCode::INTERNAL_ERROR;
            return ResponseHelper::error($code, '开票失败: ' . $e->getMessage());
        }
    }

    /**
     * 一键开票
     *
     * @param Request $request
     * @param int $receiptId
     * @return \Illuminate\Http\JsonResponse
     */
    public function invoiceAll(Request $request, int $receiptId)
    {
        $remark = $request->input('remark', '');

        try {
            $account = $this->receivableService->invoiceAll($receiptId, $remark);

            return ResponseHelper::success([
                'id' => $account->id,
                'price' => $account->price,
            ], '一键开票成功');
        } catch (\Exception $e) {
            $code = $e->getCode() ?: ErrorCode::INTERNAL_ERROR;
            return ResponseHelper::error($code, '一键开票失败: ' . $e->getMessage());
        }
    }

    /**
     * 收款
     *
     * @param Request $request
     * @param int $receiptId
     * @return \Illuminate\Http\JsonResponse
     */
    public function bill(Request $request, int $receiptId)
    {
        $data = $request->validate([
            'price' => 'required|numeric|min:0.01',
            'remark' => 'nullable|string|max:500',
        ]);

        try {
            $account = $this->receivableService->bill(
                $receiptId,
                $data['price'],
                $data['remark'] ?? ''
            );

            return ResponseHelper::success([
                'id' => $account->id,
            ], '收款成功');
        } catch (\Exception $e) {
            $code = $e->getCode() ?: ErrorCode::INTERNAL_ERROR;
            return ResponseHelper::error($code, '收款失败: ' . $e->getMessage());
        }
    }
}
