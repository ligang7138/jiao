<?php

namespace App\Services\Finance;

use App\Models\Finance\ReceivableReceipt;
use App\Models\Finance\ReceivableAccount;
use App\Models\Order\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Constants\ErrorCode;

/**
 * 应收账款服务层
 */
class ReceivableService
{
    /**
     * 获取已收货订单列表（用于生成对账单）
     */
    public function getOrderList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        // 只查询已收货状态的订单
        $query = Order::with(['school', 'canteen', 'supplier'])
            ->where('status', Order::STATUS_RECEIVED)
            ->whereNotIn('id', function ($subQuery) {
                $subQuery->select('order_id')
                    ->from('receivable_account')
                    ->where('type', ReceivableAccount::TYPE_DEBIT)
                    ->where('status', ReceivableAccount::STATUS_ENABLED)
                    ->whereNull('deleted_at');
            })
            ->search($params['keyword'] ?? null)
            ->bySchool($params['school_id'] ?? null)
            ->bySupplier($params['supplier_id'] ?? null)
            ->byDateRange($params['start_date'] ?? null, $params['end_date'] ?? null);

        $sortField = $params['sort_field'] ?? 'id';
        $sortOrder = $params['sort_order'] ?? 'desc';
        $query->orderBy($sortField, $sortOrder);

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $list->map(function ($item) {
                return [
                    'id' => $item->id,
                    'order_no' => $item->order_no,
                    'school_id' => $item->school_id,
                    'school_name' => $item->school?->school_name,
                    'canteen_id' => $item->canteen_id,
                    'canteen_name' => $item->canteen?->canteen_name,
                    'supplier_id' => $item->supplier_id,
                    'supplier_name' => $item->supplier?->supplier_name,
                    'order_date' => $item->order_date?->format('Y-m-d'),
                    'delivery_date' => $item->delivery_date?->format('Y-m-d'),
                    'total_amount' => $item->total_amount,
                    'status' => $item->status,
                    'status_text' => $item->getStatusText(),
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 获取对账单列表
     */
    public function getReceiptList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = ReceivableReceipt::with(['canteen.school', 'supplier'])
            ->search($params['keyword'] ?? null)
            ->byCanteen($params['canteen_id'] ?? null)
            ->bySupplier($params['supplier_id'] ?? null)
            ->byInvoiceStatus($params['invoice_status'] ?? null)
            ->byBillStatus($params['bill_status'] ?? null)
            ->bySchoolConfirmStatus($params['school_confirm_status'] ?? null)
            ->byDateRange($params['start_date'] ?? null, $params['end_date'] ?? null);

        $sortField = $params['sort_field'] ?? 'id';
        $sortOrder = $params['sort_order'] ?? 'desc';
        $query->orderBy($sortField, $sortOrder);

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $list->map(function ($item) {
                return [
                    'id' => $item->id,
                    'voucher_sn' => $item->voucher_sn,
                    'canteen_id' => $item->canteen_id,
                    'canteen_name' => $item->canteen?->canteen_name,
                    'school_name' => $item->canteen?->school?->school_name,
                    'supp_id' => $item->supp_id,
                    'supplier_name' => $item->supplier?->supplier_name,
                    'debit_price' => $item->debit_price,
                    'credit_price' => $item->credit_price,
                    'uninvoiced_amount' => $item->getUninvoicedAmount(),
                    'unbilled_amount' => $item->getUnbilledAmount(),
                    'invoice_status' => $item->invoice_status,
                    'invoice_status_text' => $item->getInvoiceStatusText(),
                    'bill_status' => $item->bill_status,
                    'bill_status_text' => $item->getBillStatusText(),
                    'school_confirm_status' => $item->school_confirm_status,
                    'school_confirm_status_text' => $item->getSchoolConfirmStatusText(),
                    'invoice_time' => $item->invoice_time?->format('Y-m-d H:i:s'),
                    'bill_time' => $item->bill_time?->format('Y-m-d H:i:s'),
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 获取对账单详情
     */
    public function getReceiptDetail(int $id): array
    {
        $receipt = ReceivableReceipt::with(['canteen.school', 'supplier', 'accounts.order'])
            ->findOrFail($id);

        return [
            'id' => $receipt->id,
            'voucher_sn' => $receipt->voucher_sn,
            'canteen_id' => $receipt->canteen_id,
            'canteen_name' => $receipt->canteen?->canteen_name,
            'school_id' => $receipt->canteen?->school_id,
            'school_name' => $receipt->canteen?->school?->school_name,
            'supp_id' => $receipt->supp_id,
            'supplier_name' => $receipt->supplier?->supplier_name,
            'debit_price' => $receipt->debit_price,
            'credit_price' => $receipt->credit_price,
            'uninvoiced_amount' => $receipt->getUninvoicedAmount(),
            'unbilled_amount' => $receipt->getUnbilledAmount(),
            'invoice_status' => $receipt->invoice_status,
            'invoice_status_text' => $receipt->getInvoiceStatusText(),
            'bill_status' => $receipt->bill_status,
            'bill_status_text' => $receipt->getBillStatusText(),
            'school_confirm_status' => $receipt->school_confirm_status,
            'school_confirm_status_text' => $receipt->getSchoolConfirmStatusText(),
            'invoice_time' => $receipt->invoice_time?->format('Y-m-d H:i:s'),
            'bill_time' => $receipt->bill_time?->format('Y-m-d H:i:s'),
            'remark' => $receipt->remark,
            'accounts' => $receipt->accounts->map(function ($item) {
                return [
                    'id' => $item->id,
                    'order_id' => $item->order_id,
                    'order_no' => $item->order?->order_no,
                    'type' => $item->type,
                    'type_text' => $item->getTypeText(),
                    'price' => $item->price,
                    'status' => $item->status,
                    'status_text' => $item->getStatusText(),
                    'remark' => $item->remark,
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'created_at' => $receipt->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 创建对账单
     */
    public function createReceipt(array $data, array $orderIds = []): ReceivableReceipt
    {
        DB::beginTransaction();
        try {
            // 生成凭证号
            if (!isset($data['voucher_sn'])) {
                $data['voucher_sn'] = $this->generateVoucherSn();
            }

            // 默认状态
            $data['invoice_status'] = $data['invoice_status'] ?? ReceivableReceipt::INVOICE_STATUS_PENDING;
            $data['bill_status'] = $data['bill_status'] ?? ReceivableReceipt::BILL_STATUS_PENDING;
            $data['school_confirm_status'] = $data['school_confirm_status'] ?? ReceivableReceipt::SCHOOL_CONFIRM_PENDING;

            // 计算借方金额（应收金额）
            $debitPrice = 0;
            $orders = [];
            if (!empty($orderIds)) {
                $orders = Order::whereIn('id', $orderIds)
                    ->where('status', Order::STATUS_RECEIVED)
                    ->get();
                $debitPrice = $orders->sum('total_amount');
            }
            $data['debit_price'] = $debitPrice;
            $data['credit_price'] = 0;

            $receipt = ReceivableReceipt::create($data);

            // 创建账单明细（借方）
            foreach ($orders as $order) {
                ReceivableAccount::create([
                    'receipt_id' => $receipt->id,
                    'order_id' => $order->id,
                    'type' => ReceivableAccount::TYPE_DEBIT,
                    'price' => $order->total_amount,
                    'status' => ReceivableAccount::STATUS_ENABLED,
                    'remark' => '订单对账',
                ]);
            }

            DB::commit();
            return $receipt;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 调整对账单
     */
    public function adjustReceipt(int $id, array $data): ReceivableReceipt
    {
        DB::beginTransaction();
        try {
            $receipt = ReceivableReceipt::findOrFail($id);

            // 不允许调整已完成的收款状态
            if ($receipt->bill_status === ReceivableReceipt::BILL_STATUS_COMPLETED) {
                throw new \Exception('已收款的账单不允许调整', ErrorCode::BUSINESS_CONDITION_NOT_MET);
            }

            $receipt->update($data);

            DB::commit();
            return $receipt->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 获取账单明细列表
     */
    public function getAccountList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = ReceivableAccount::with(['receipt', 'order'])
            ->byReceipt($params['receipt_id'] ?? null)
            ->byType($params['type'] ?? null)
            ->byStatus($params['status'] ?? null);

        $sortField = $params['sort_field'] ?? 'id';
        $sortOrder = $params['sort_order'] ?? 'desc';
        $query->orderBy($sortField, $sortOrder);

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $list->map(function ($item) {
                return [
                    'id' => $item->id,
                    'receipt_id' => $item->receipt_id,
                    'voucher_sn' => $item->receipt?->voucher_sn,
                    'order_id' => $item->order_id,
                    'order_no' => $item->order?->order_no,
                    'type' => $item->type,
                    'type_text' => $item->getTypeText(),
                    'price' => $item->price,
                    'status' => $item->status,
                    'status_text' => $item->getStatusText(),
                    'remark' => $item->remark,
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 新增账单明细
     */
    public function addAccount(array $data): ReceivableAccount
    {
        DB::beginTransaction();
        try {
            $receipt = ReceivableReceipt::findOrFail($data['receipt_id']);

            // 默认状态
            $data['status'] = $data['status'] ?? ReceivableAccount::STATUS_ENABLED;

            $account = ReceivableAccount::create($data);

            // 更新对账单金额
            $this->updateReceiptAmounts($receipt);

            DB::commit();
            return $account;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 删除账单明细
     */
    public function deleteAccount(int $id): bool
    {
        DB::beginTransaction();
        try {
            $account = ReceivableAccount::findOrFail($id);
            $receipt = $account->receipt;

            $result = $account->delete();

            // 更新对账单金额
            $this->updateReceiptAmounts($receipt);

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 开票
     */
    public function invoice(int $receiptId, float $price, string $remark = ''): ReceivableAccount
    {
        DB::beginTransaction();
        try {
            $receipt = ReceivableReceipt::findOrFail($receiptId);

            // 检查未开票金额
            $uninvoicedAmount = $receipt->getUninvoicedAmount();
            if ($price > $uninvoicedAmount) {
                throw new \Exception('开票金额不能超过未开票金额', ErrorCode::BUSINESS_LIMIT);
            }

            // 创建开票记录
            $account = ReceivableAccount::create([
                'receipt_id' => $receiptId,
                'order_id' => null,
                'type' => ReceivableAccount::TYPE_INVOICE,
                'price' => $price,
                'status' => ReceivableAccount::STATUS_ENABLED,
                'remark' => $remark ?: '开票',
            ]);

            // 更新对账单贷方金额
            $receipt->credit_price = $receipt->credit_price + $price;
            $receipt->invoice_time = now();

            // 更新开票状态
            if ($receipt->credit_price >= $receipt->debit_price) {
                $receipt->invoice_status = ReceivableReceipt::INVOICE_STATUS_COMPLETED;
            } else {
                $receipt->invoice_status = ReceivableReceipt::INVOICE_STATUS_PARTIAL;
            }

            $receipt->save();

            DB::commit();
            return $account;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 一键开票（全额开票）
     */
    public function invoiceAll(int $receiptId, string $remark = ''): ReceivableAccount
    {
        $receipt = ReceivableReceipt::findOrFail($receiptId);
        $uninvoicedAmount = $receipt->getUninvoicedAmount();

        if ($uninvoicedAmount <= 0) {
            throw new \Exception('没有需要开票的金额', ErrorCode::BUSINESS_CONDITION_NOT_MET);
        }

        return $this->invoice($receiptId, $uninvoicedAmount, $remark ?: '一键全额开票');
    }

    /**
     * 收款
     */
    public function bill(int $receiptId, float $price, string $remark = ''): ReceivableAccount
    {
        DB::beginTransaction();
        try {
            $receipt = ReceivableReceipt::findOrFail($receiptId);

            // 检查已开票金额
            $invoicedAmount = $receipt->credit_price;
            $billedAmount = $receipt->getBilledAmount();
            $unbilledAmount = $invoicedAmount - $billedAmount;

            if ($price > $unbilledAmount) {
                throw new \Exception('收款金额不能超过未收款金额', ErrorCode::BUSINESS_LIMIT);
            }

            // 创建收款记录
            $account = ReceivableAccount::create([
                'receipt_id' => $receiptId,
                'order_id' => null,
                'type' => ReceivableAccount::TYPE_BILL,
                'price' => $price,
                'status' => ReceivableAccount::STATUS_ENABLED,
                'remark' => $remark ?: '收款',
            ]);

            // 更新收款状态
            $newBilledAmount = $billedAmount + $price;
            $receipt->bill_time = now();

            if ($newBilledAmount >= $invoicedAmount) {
                $receipt->bill_status = ReceivableReceipt::BILL_STATUS_COMPLETED;
            } else {
                $receipt->bill_status = ReceivableReceipt::BILL_STATUS_PARTIAL;
            }

            $receipt->save();

            DB::commit();
            return $account;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 更新对账单金额
     */
    private function updateReceiptAmounts(ReceivableReceipt $receipt): void
    {
        // 重新计算借方金额
        $debitPrice = $receipt->accounts()
            ->where('type', ReceivableAccount::TYPE_DEBIT)
            ->where('status', ReceivableAccount::STATUS_ENABLED)
            ->sum('price');

        // 重新计算贷方金额（开票金额）
        $creditPrice = $receipt->accounts()
            ->where('type', ReceivableAccount::TYPE_INVOICE)
            ->where('status', ReceivableAccount::STATUS_ENABLED)
            ->sum('price');

        $receipt->debit_price = $debitPrice;
        $receipt->credit_price = $creditPrice;

        // 更新开票状态
        if ($creditPrice >= $debitPrice && $debitPrice > 0) {
            $receipt->invoice_status = ReceivableReceipt::INVOICE_STATUS_COMPLETED;
        } elseif ($creditPrice > 0) {
            $receipt->invoice_status = ReceivableReceipt::INVOICE_STATUS_PARTIAL;
        } else {
            $receipt->invoice_status = ReceivableReceipt::INVOICE_STATUS_PENDING;
        }

        // 更新收款状态
        $billedAmount = $receipt->getBilledAmount();
        if ($billedAmount >= $creditPrice && $creditPrice > 0) {
            $receipt->bill_status = ReceivableReceipt::BILL_STATUS_COMPLETED;
        } elseif ($billedAmount > 0) {
            $receipt->bill_status = ReceivableReceipt::BILL_STATUS_PARTIAL;
        } else {
            $receipt->bill_status = ReceivableReceipt::BILL_STATUS_PENDING;
        }

        $receipt->save();
    }

    /**
     * 生成凭证号
     */
    private function generateVoucherSn(): string
    {
        return 'RC' . date('YmdHis') . Str::random(4);
    }
}
