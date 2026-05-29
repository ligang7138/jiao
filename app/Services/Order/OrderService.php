<?php

namespace App\Services\Order;

use App\Models\Order\Order;
use App\Models\Order\OrderGoods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * 订单服务层
 */
class OrderService
{
    /** @var array<string, bool>|null */
    private static ?array $ordersGoodsColumnMap = null;

    /**
     * 获取订单列表（与 admin/order/index.php 查询逻辑一致）
     */
    public function getList(array $params): array
    {
        $page = (int) ($params['page'] ?? 1);
        $pageSize = (int) ($params['page_size'] ?? 20);

        $query = Order::query()
            ->search($params['keyword'] ?? null)
            ->applyAdminListFilters($params);

        $sortField = $params['sort_field'] ?? 'id';
        $sortOrder = $params['sort_order'] ?? 'desc';
        $query->orderBy($sortField, $sortOrder);

        $total = (clone $query)->count();
        $orders = (clone $query)
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $orders->map(fn (Order $order) => $this->formatAdminListItem($order)),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
            'statistics' => $this->calculateAdminListStatistics($params),
        ];
    }

    /**
     * 列表行格式化：保留 orders 表字段，并汇总商品金额（与旧系统 foreach 一致）
     */
    private function formatAdminListItem(Order $order): array
    {
        $row = $order->toArray();

        if ($order->send_date) {
            $row['send_date'] = $order->send_date->format('Y-m-d');
        }

        $goodsStats = $this->aggregateOrderGoodsStats($order->id);

        if ($goodsStats) {
            $row['need_num'] = (int) ($goodsStats['need_num'] ?? 0);
            $row['send_price'] = $goodsStats['send_price'] ?? $row['send_price'] ?? 0;
            $row['receive_price'] = $goodsStats['receive_price'] ?? $row['receive_price'] ?? 0;
            $row['back_price'] = $goodsStats['back_price'] ?? $row['back_price'] ?? 0;
            $row['settle_price'] = $goodsStats['settle_price'] ?? $row['settle_price'] ?? 0;
        }

        return $row;
    }

    /**
     * 汇总订单商品金额（按实际表字段动态拼接，兼容无 settleqty 的旧库）
     *
     * @return array<string, float|int>|null
     */
    private function aggregateOrderGoodsStats(int $orderId): ?array
    {
        $selects = ['count(*) as need_num'];

        if ($this->ordersGoodsHasColumn('sendqty') && $this->ordersGoodsHasColumn('sale_price')) {
            $selects[] = 'sum(round(sendqty * sale_price, 2)) as send_price';
        }
        if ($this->ordersGoodsHasColumn('receiveqty') && $this->ordersGoodsHasColumn('sale_price')) {
            $selects[] = 'sum(round(receiveqty * sale_price, 2)) as receive_price';
        }
        if ($this->ordersGoodsHasColumn('backqty') && $this->ordersGoodsHasColumn('sale_price')) {
            $selects[] = 'sum(round(backqty * sale_price, 2)) as back_price';
        }
        if ($this->ordersGoodsHasColumn('settleqty') && $this->ordersGoodsHasColumn('sale_price')) {
            $selects[] = 'sum(round(settleqty * sale_price, 2)) as settle_price';
        }

        $row = DB::table('orders_goods')
            ->where('order_id', $orderId)
            ->selectRaw(implode(', ', $selects))
            ->first();

        if (!$row) {
            return null;
        }

        $result = [
            'need_num' => (int) ($row->need_num ?? 0),
            'send_price' => isset($row->send_price) ? (float) $row->send_price : null,
            'receive_price' => isset($row->receive_price) ? (float) $row->receive_price : null,
            'back_price' => isset($row->back_price) ? (float) $row->back_price : null,
            'settle_price' => isset($row->settle_price) ? (float) $row->settle_price : null,
        ];

        return $result;
    }

    private function ordersGoodsHasColumn(string $column): bool
    {
        if (self::$ordersGoodsColumnMap === null) {
            self::$ordersGoodsColumnMap = array_fill_keys(
                Schema::getColumnListing('orders_goods'),
                true
            );
        }

        return isset(self::$ordersGoodsColumnMap[$column]);
    }

    /**
     * 列表页统计（与旧系统 index.php 底部统计一致，仅统计 status > 10）
     */
    private function calculateAdminListStatistics(array $params): array
    {
        $statsQuery = Order::query()
            ->search($params['keyword'] ?? null)
            ->applyAdminListFilters($params)
            ->where('status', '>', 10);

        $orderStats = (clone $statsQuery)
            ->selectRaw('count(distinct canteen_id) as canteen_amount')
            ->selectRaw('ifnull(sum(total_price), 0) as total_amount')
            ->selectRaw('count(id) as order_count')
            ->first();

        $canteenIds = (clone $statsQuery)->distinct()->pluck('canteen_id');

        $schoolAmount = 0;
        if ($canteenIds->isNotEmpty()) {
            $schoolAmount = DB::table('school_canteen')
                ->whereIn('id', $canteenIds)
                ->distinct('school_id')
                ->count('school_id');
        }

        return [
            'order_count' => (int) ($orderStats->order_count ?? 0),
            'school_amount' => $schoolAmount,
            'canteen_amount' => (int) ($orderStats->canteen_amount ?? 0),
            'total_amount' => round((float) ($orderStats->total_amount ?? 0), 2),
        ];
    }

    /**
     * 创建订单
     */
    public function create(array $data, array $goods = []): Order
    {
        DB::beginTransaction();
        try {
            // 生成订单号
            if (!isset($data['order_no'])) {
                $data['order_no'] = $this->generateOrderNo();
            }

            // 默认状态
            if (!isset($data['status'])) {
                $data['status'] = Order::STATUS_DRAFT;
            }

            // 计算总金额
            $totalAmount = 0;
            foreach ($goods as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
            }
            $data['total_amount'] = $totalAmount;

            $order = Order::create($data);

            // 创建订单商品
            foreach ($goods as $item) {
                OrderGoods::create([
                    'order_id' => $order->id,
                    'goods_id' => $item['goods_id'] ?? null,
                    'goods_name' => $item['goods_name'],
                    'unit' => $item['unit'],
                    'spec' => $item['spec'] ?? '',
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'amount' => $item['price'] * $item['quantity'],
                    'remark' => $item['remark'] ?? '',
                ]);
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 更新订单
     */
    public function update(int $id, array $data, array $goods = []): Order
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);

            // 计算总金额
            if (!empty($goods)) {
                $totalAmount = 0;
                foreach ($goods as $item) {
                    $totalAmount += $item['price'] * $item['quantity'];
                }
                $data['total_amount'] = $totalAmount;
            }

            $order->update($data);

            // 更新订单商品
            if (!empty($goods)) {
                // 删除原有商品
                $order->goods()->delete();

                // 重新创建商品
                foreach ($goods as $item) {
                    OrderGoods::create([
                        'order_id' => $order->id,
                        'goods_id' => $item['goods_id'] ?? null,
                        'goods_name' => $item['goods_name'],
                        'unit' => $item['unit'],
                        'spec' => $item['spec'] ?? '',
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'amount' => $item['price'] * $item['quantity'],
                        'remark' => $item['remark'] ?? '',
                    ]);
                }
            }

            DB::commit();
            return $order->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 删除订单
     */
    public function delete(int $id): bool
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);

            // 删除订单商品
            $order->goods()->delete();

            // 删除订单
            $result = $order->delete();

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 获取订单详情
     */
    public function getDetail(int $id): array
    {
        $order = Order::with(['school', 'canteen', 'supplier', 'goods'])->findOrFail($id);

        return [
            'id' => $order->id,
            'order_no' => $order->order_no,
            'school_id' => $order->school_id,
            'school_name' => $order->school?->school_name,
            'canteen_id' => $order->canteen_id,
            'canteen_name' => $order->canteen?->canteen_name,
            'supplier_id' => $order->supplier_id,
            'supplier_name' => $order->supplier?->supplier_name,
            'order_date' => $order->order_date?->format('Y-m-d'),
            'delivery_date' => $order->delivery_date?->format('Y-m-d'),
            'total_amount' => $order->total_amount,
            'status' => $order->status,
            'status_text' => $order->getStatusText(),
            'remark' => $order->remark,
            'goods' => $order->goods->map(function ($item) {
                return [
                    'id' => $item->id,
                    'goods_id' => $item->goods_id,
                    'goods_name' => $item->goods_name,
                    'unit' => $item->unit,
                    'spec' => $item->spec,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'amount' => $item->amount,
                ];
            }),
            'created_at' => $order->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 更改订单状态
     */
    public function changeStatus(int $id, int $status): Order
    {
        $order = Order::findOrFail($id);
        $order->status = $status;
        $order->save();

        return $order;
    }

    /**
     * 生成订单号
     */
    private function generateOrderNo(): string
    {
        return 'PO' . date('YmdHis') . Str::random(4);
    }

    /**
     * 订单导出
     */
    public function export(array $params): string
    {
        $query = Order::with(['school', 'canteen', 'supplier'])
            ->search($params['keyword'] ?? null)
            ->bySchool($params['school_id'] ?? null)
            ->bySupplier($params['supplier_id'] ?? null)
            ->byStatus($params['status'] ?? null)
            ->byDateRange($params['start_date'] ?? null, $params['end_date'] ?? null);

        $orders = $query->orderBy('id', 'desc')->get();

        // 表头
        $headers = [
            '订单编号',
            '学校名称',
            '食堂名称',
            '供应商',
            '订单日期',
            '送货日期',
            '订单金额',
            '状态',
            '创建时间',
        ];

        // 数据
        $data = $orders->map(function ($item) {
            return [
                $item->order_no ?? '',
                $item->school?->school_name ?? '',
                $item->canteen?->canteen_name ?? '',
                $item->supplier?->supplier_name ?? '',
                $item->order_date ?? '',
                $item->delivery_date ?? '',
                $item->total_amount ?? 0,
                $item->getStatusText(),
                $item->created_at?->format('Y-m-d H:i:s') ?? '',
            ];
        })->toArray();

        $excelService = new \App\Services\Common\ExcelExportService();
        return $excelService->export($headers, $data, 'orders_export');
    }

    /**
     * 订单明细导出
     */
    public function exportDetail(array $params): string
    {
        $query = Order::with(['school', 'canteen', 'supplier', 'goods'])
            ->search($params['keyword'] ?? null)
            ->bySchool($params['school_id'] ?? null)
            ->bySupplier($params['supplier_id'] ?? null)
            ->byStatus($params['status'] ?? null)
            ->byDateRange($params['start_date'] ?? null, $params['end_date'] ?? null);

        $orders = $query->orderBy('id', 'desc')->get();

        // 表头
        $headers = [
            '订单编号',
            '学校名称',
            '食堂名称',
            '供应商',
            '商品名称',
            '规格',
            '单位',
            '单价',
            '数量',
            '金额',
            '订单日期',
        ];

        // 数据 - 展开订单商品明细
        $data = [];
        foreach ($orders as $order) {
            foreach ($order->goods as $goods) {
                $data[] = [
                    $order->order_no ?? '',
                    $order->school?->school_name ?? '',
                    $order->canteen?->canteen_name ?? '',
                    $order->supplier?->supplier_name ?? '',
                    $goods->goods_name ?? '',
                    $goods->spec ?? '',
                    $goods->unit ?? '',
                    $goods->price ?? 0,
                    $goods->quantity ?? 0,
                    $goods->amount ?? ($goods->price * $goods->quantity),
                    $order->order_date ?? '',
                ];
            }
        }

        $excelService = new \App\Services\Common\ExcelExportService();
        return $excelService->export($headers, $data, 'orders_detail_export');
    }

    /**
     * 订单统计
     */
    public function getStatistics(array $params): array
    {
        $query = Order::query()
            ->search($params['keyword'] ?? null)
            ->bySchool($params['school_id'] ?? null)
            ->bySupplier($params['supplier_id'] ?? null)
            ->byStatus($params['status'] ?? null)
            ->byDateRange($params['start_date'] ?? null, $params['end_date'] ?? null);

        $orders = $query->get();

        // 统计数据
        $orderCount = $orders->count();
        $schoolAmount = $orders->unique('school_id')->count();
        $canteenAmount = $orders->unique('canteen_id')->count();
        $totalAmount = $orders->sum('total_amount');

        return [
            'order_count' => $orderCount,
            'school_amount' => $schoolAmount,
            'canteen_amount' => $canteenAmount,
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * 溯源信息
     */
    public function getTraceSource(int $id): array
    {
        $order = Order::with(['school', 'canteen', 'supplier', 'goods'])->findOrFail($id);

        return [
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'school' => [
                'id' => $order->school_id,
                'name' => $order->school?->school_name,
            ],
            'canteen' => [
                'id' => $order->canteen_id,
                'name' => $order->canteen?->canteen_name,
            ],
            'supplier' => [
                'id' => $order->supplier_id,
                'name' => $order->supplier?->supplier_name,
            ],
            'delivery_date' => $order->delivery_date?->format('Y-m-d'),
            'goods' => $order->goods->map(function ($item) {
                return [
                    'goods_id' => $item->goods_id,
                    'goods_name' => $item->goods_name,
                    'spec' => $item->spec,
                    'unit' => $item->unit,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            }),
            'trace_code' => 'TR' . $order->id . date('Ymd', strtotime($order->created_at)),
            'created_at' => $order->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}