<?php

namespace App\Services\Jiagewang;

use App\Models\Jiagewang\GoodsJiagewang;
use App\Models\Jiagewang\GoodsJiagewangLog;
use App\Models\Goods\Goods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * 价格网服务层
 */
class JiagewangService
{
    /**
     * 获取指导价列表
     */
    public function getList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        // 使用 RIGHT JOIN 获取所有商品，包括未设置指导价的商品
        $query = DB::table('goods_jiagewang as gj')
            ->rightJoin('goods as g', 'gj.goods_id', '=', 'g.id')
            ->select(
                'gj.id',
                'gj.goods_id',
                'gj.goods_sn',
                'gj.name',
                'gj.cate_name',
                'gj.scate_name',
                'gj.price',
                'gj.update_date',
                'gj.update_user',
                'gj.update_time',
                'g.id as good_id',
                'g.goods_sn as g_goods_sn',
                'g.goods_name',
                'g.status',
                'g.cate_id',
                'g.scate_id'
            );

        // 搜索条件
        if (!empty($params['goods_sn'])) {
            $query->where('g.goods_sn', $params['goods_sn']);
        }

        if (!empty($params['goods_name'])) {
            $query->where('g.goods_name', 'like', "%{$params['goods_name']}%");
        }

        if (!empty($params['cate_id'])) {
            $query->where('g.cate_id', $params['cate_id']);
        }

        if (!empty($params['scate_id'])) {
            $query->where('g.scate_id', $params['scate_id']);
        }

        // 排序：未设置指导价的商品优先显示
        $query->orderByRaw('gj.price IS NULL DESC')
            ->orderBy('gj.update_time', 'desc')
            ->orderBy('g.id', 'desc');

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $list->map(function ($item) {
                return [
                    'id' => $item->id,
                    'goods_id' => $item->goods_id ?? $item->good_id,
                    'goods_sn' => $item->goods_sn ?? $item->g_goods_sn,
                    'goods_name' => $item->goods_name ?? $item->name,
                    'cate_name' => $item->cate_name,
                    'scate_name' => $item->scate_name,
                    'price' => $item->price,
                    'update_date' => $item->update_date,
                    'update_user' => $item->update_user,
                    'update_time' => $item->update_time ? date('Y-m-d H:i:s', $item->update_time) : null,
                    'status' => $item->status,
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 导入指导价
     */
    public function import($file, int $userId, string $userName): array
    {
        $path = $file->getRealPath();
        $extension = $file->getClientOriginalExtension();

        // 读取Excel文件
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // 移除表头
        array_shift($rows);

        $successCount = 0;
        $errorList = [];
        $updateDate = date('Y-m-d');
        $updateTime = time();

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // Excel行号（从第2行开始）

                $goodsSn = trim($row[0] ?? ''); // A列：商品编码
                $price = isset($row[11]) ? floatval($row[11]) : 0; // L列：指导价（索引11）

                // 验证商品编码
                if (empty($goodsSn)) {
                    $errorList[] = [
                        'row' => $rowNumber,
                        'goods_sn' => $goodsSn,
                        'price' => $price,
                        'type' => 1,
                        'message' => '商品编码为空',
                    ];
                    continue;
                }

                // 查询商品
                $goods = Goods::where('goods_sn', $goodsSn)->first();
                if (!$goods) {
                    $errorList[] = [
                        'row' => $rowNumber,
                        'goods_sn' => $goodsSn,
                        'price' => $price,
                        'type' => 1,
                        'message' => '商品不存在',
                    ];
                    continue;
                }

                // 验证价格
                if ($price <= 0) {
                    $errorList[] = [
                        'row' => $rowNumber,
                        'goods_sn' => $goodsSn,
                        'price' => $price,
                        'type' => 2,
                        'message' => '价格无效（必须大于0）',
                    ];
                    continue;
                }

                // 获取分类名称
                $cateName = null;
                $scateName = null;
                if ($goods->cate_id) {
                    $cate = DB::table('category')->where('id', $goods->cate_id)->first();
                    $cateName = $cate->name ?? null;
                }
                if ($goods->scate_id) {
                    $scate = DB::table('category')->where('id', $goods->scate_id)->first();
                    $scateName = $scate->name ?? null;
                }

                // 更新或插入指导价
                $jiagewang = GoodsJiagewang::where('goods_id', $goods->id)->first();
                $oldPrice = $jiagewang->price ?? null;

                if ($jiagewang) {
                    // 更新
                    $jiagewang->update([
                        'goods_sn' => $goodsSn,
                        'name' => $goods->goods_name,
                        'cate_name' => $cateName,
                        'scate_name' => $scateName,
                        'price' => $price,
                        'update_date' => $updateDate,
                        'update_user' => $userName,
                        'update_time' => $updateTime,
                    ]);
                } else {
                    // 插入
                    GoodsJiagewang::create([
                        'goods_id' => $goods->id,
                        'goods_sn' => $goodsSn,
                        'name' => $goods->goods_name,
                        'cate_name' => $cateName,
                        'scate_name' => $scateName,
                        'price' => $price,
                        'update_date' => $updateDate,
                        'update_user' => $userName,
                        'update_time' => $updateTime,
                    ]);
                }

                // 记录日志
                GoodsJiagewangLog::create([
                    'goods_id' => $goods->id,
                    'goods_sn' => $goodsSn,
                    'name' => $goods->goods_name,
                    'cate_name' => $cateName,
                    'scate_name' => $scateName,
                    'price' => $price,
                    'update_date' => $updateDate,
                    'update_user' => $userName,
                    'update_time' => $updateTime,
                ]);

                $successCount++;
            }

            DB::commit();

            // 存储错误数据到Redis（5分钟过期）
            if (!empty($errorList)) {
                $errorKey = 'jiagewang_import_error:' . $userId . ':' . time();
                Redis::setex($errorKey, 300, json_encode($errorList));
            }

            // 发送通知给供应商（异步处理）
            $this->sendNotificationToSuppliers();

            return [
                'success_count' => $successCount,
                'error_count' => count($errorList),
                'error_list' => $errorList,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 编辑指导价
     */
    public function updatePrice(int $id, string $goodsSn, int $goodsId, float $price, string $userName): bool
    {
        $updateDate = date('Y-m-d');
        $updateTime = time();

        // 查询商品
        $goods = Goods::findOrFail($goodsId);

        // 获取分类名称
        $cateName = null;
        $scateName = null;
        if ($goods->cate_id) {
            $cate = DB::table('category')->where('id', $goods->cate_id)->first();
            $cateName = $cate->name ?? null;
        }
        if ($goods->scate_id) {
            $scate = DB::table('category')->where('id', $goods->scate_id)->first();
            $scateName = $scate->name ?? null;
        }

        return DB::transaction(function () use ($id, $goodsSn, $goodsId, $price, $userName, $updateDate, $updateTime, $goods, $cateName, $scateName) {
            // 检查是否存在指导价记录
            $jiagewang = $id > 0 ? GoodsJiagewang::find($id) : GoodsJiagewang::where('goods_id', $goodsId)->first();

            if ($jiagewang) {
                // 更新
                $jiagewang->update([
                    'goods_sn' => $goodsSn,
                    'name' => $goods->goods_name,
                    'cate_name' => $cateName,
                    'scate_name' => $scateName,
                    'price' => $price,
                    'update_date' => $updateDate,
                    'update_user' => $userName,
                    'update_time' => $updateTime,
                ]);
            } else {
                // 插入
                GoodsJiagewang::create([
                    'goods_id' => $goodsId,
                    'goods_sn' => $goodsSn,
                    'name' => $goods->goods_name,
                    'cate_name' => $cateName,
                    'scate_name' => $scateName,
                    'price' => $price,
                    'update_date' => $updateDate,
                    'update_user' => $userName,
                    'update_time' => $updateTime,
                ]);
            }

            // 记录日志
            GoodsJiagewangLog::create([
                'goods_id' => $goodsId,
                'goods_sn' => $goodsSn,
                'name' => $goods->goods_name,
                'cate_name' => $cateName,
                'scate_name' => $scateName,
                'price' => $price,
                'update_date' => $updateDate,
                'update_user' => $userName,
                'update_time' => $updateTime,
            ]);

            // 发送通知给供应商
            $this->sendNotificationToSuppliers();

            return true;
        });
    }

    /**
     * 获取历史记录列表
     */
    public function getHistoryList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = GoodsJiagewangLog::query();

        // 搜索条件
        if (!empty($params['goods_sn'])) {
            $query->where('goods_sn', $params['goods_sn']);
        }

        if (!empty($params['goods_name'])) {
            $query->where('name', 'like', "%{$params['goods_name']}%");
        }

        if (!empty($params['cate_name'])) {
            $query->where('cate_name', $params['cate_name']);
        }

        if (!empty($params['scate_name'])) {
            $query->where('scate_name', $params['scate_name']);
        }

        // 日期范围
        if (!empty($params['start_date'])) {
            $query->where('update_date', '>=', $params['start_date']);
        }
        if (!empty($params['end_date'])) {
            $query->where('update_date', '<=', $params['end_date']);
        }

        // 排序
        $query->orderBy('update_time', 'desc');

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $list->map(function ($item) {
                return [
                    'id' => $item->id,
                    'goods_id' => $item->goods_id,
                    'goods_sn' => $item->goods_sn,
                    'goods_name' => $item->name,
                    'cate_name' => $item->cate_name,
                    'scate_name' => $item->scate_name,
                    'price' => $item->price,
                    'update_date' => $item->update_date,
                    'update_user' => $item->update_user,
                    'update_time' => $item->update_time ? date('Y-m-d H:i:s', $item->update_time) : null,
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 获取商品匹配列表（已匹配指导价的商品）
     */
    public function getMatchList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = DB::table('goods_jiagewang as gj')
            ->join('goods as g', 'gj.goods_id', '=', 'g.id')
            ->select(
                'gj.id',
                'gj.goods_id',
                'gj.goods_sn',
                'gj.name',
                'gj.cate_name',
                'gj.scate_name',
                'gj.price',
                'gj.update_date',
                'gj.update_user',
                'gj.update_time',
                'g.goods_name',
                'g.status'
            );

        // 搜索条件
        if (!empty($params['goods_sn'])) {
            $query->where('gj.goods_sn', $params['goods_sn']);
        }

        if (!empty($params['goods_name'])) {
            $query->where('gj.name', 'like', "%{$params['goods_name']}%");
        }

        if (!empty($params['cate_name'])) {
            $query->where('gj.cate_name', $params['cate_name']);
        }

        if (!empty($params['scate_name'])) {
            $query->where('gj.scate_name', $params['scate_name']);
        }

        // 排序
        $query->orderBy('gj.update_time', 'desc');

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $list->map(function ($item) {
                return [
                    'id' => $item->id,
                    'goods_id' => $item->goods_id,
                    'goods_sn' => $item->goods_sn,
                    'goods_name' => $item->goods_name ?? $item->name,
                    'cate_name' => $item->cate_name,
                    'scate_name' => $item->scate_name,
                    'price' => $item->price,
                    'update_date' => $item->update_date,
                    'update_user' => $item->update_user,
                    'update_time' => $item->update_time ? date('Y-m-d H:i:s', $item->update_time) : null,
                    'status' => $item->status,
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 获取未匹配商品列表（未设置指导价的商品）
     */
    public function getNoMatchList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = DB::table('goods as g')
            ->leftJoin('goods_jiagewang as gj', 'g.id', '=', 'gj.goods_id')
            ->whereNull('gj.id')
            ->select(
                'g.id as goods_id',
                'g.goods_sn',
                'g.goods_name',
                'g.cate_id',
                'g.scate_id',
                'g.status'
            );

        // 搜索条件
        if (!empty($params['goods_sn'])) {
            $query->where('g.goods_sn', $params['goods_sn']);
        }

        if (!empty($params['goods_name'])) {
            $query->where('g.goods_name', 'like', "%{$params['goods_name']}%");
        }

        if (!empty($params['cate_id'])) {
            $query->where('g.cate_id', $params['cate_id']);
        }

        if (!empty($params['scate_id'])) {
            $query->where('g.scate_id', $params['scate_id']);
        }

        // 排序
        $query->orderBy('g.id', 'desc');

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        // 获取分类名称
        $categoryIds = $list->pluck('cate_id')->unique()->filter()
            ->merge($list->pluck('scate_id')->unique()->filter())
            ->toArray();

        $categories = [];
        if (!empty($categoryIds)) {
            $categories = DB::table('category')
                ->whereIn('id', $categoryIds)
                ->pluck('name', 'id')
                ->toArray();
        }

        return [
            'list' => $list->map(function ($item) use ($categories) {
                return [
                    'goods_id' => $item->goods_id,
                    'goods_sn' => $item->goods_sn,
                    'goods_name' => $item->goods_name,
                    'cate_id' => $item->cate_id,
                    'cate_name' => $categories[$item->cate_id] ?? null,
                    'scate_id' => $item->scate_id,
                    'scate_name' => $categories[$item->scate_id] ?? null,
                    'status' => $item->status,
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 导出指导价列表
     */
    public function export(array $params): array
    {
        $query = DB::table('goods_jiagewang as gj')
            ->rightJoin('goods as g', 'gj.goods_id', '=', 'g.id')
            ->select(
                'gj.id',
                'gj.goods_id',
                'gj.goods_sn',
                'gj.name',
                'gj.cate_name',
                'gj.scate_name',
                'gj.price',
                'gj.update_date',
                'gj.update_user',
                'gj.update_time',
                'g.id as good_id',
                'g.goods_sn as g_goods_sn',
                'g.goods_name',
                'g.status'
            );

        // 搜索条件
        if (!empty($params['goods_sn'])) {
            $query->where('g.goods_sn', $params['goods_sn']);
        }

        if (!empty($params['goods_name'])) {
            $query->where('g.goods_name', 'like', "%{$params['goods_name']}%");
        }

        if (!empty($params['cate_id'])) {
            $query->where('g.cate_id', $params['cate_id']);
        }

        if (!empty($params['scate_id'])) {
            $query->where('g.scate_id', $params['scate_id']);
        }

        // 排序
        $query->orderByRaw('gj.price IS NULL DESC')
            ->orderBy('gj.update_time', 'desc');

        $list = $query->get();

        return $list->map(function ($item) {
            return [
                'goods_sn' => $item->goods_sn ?? $item->g_goods_sn,
                'goods_name' => $item->goods_name ?? $item->name,
                'cate_name' => $item->cate_name ?? '',
                'scate_name' => $item->scate_name ?? '',
                'price' => $item->price ?? '',
                'update_date' => $item->update_date ?? '',
                'update_user' => $item->update_user ?? '',
            ];
        })->toArray();
    }

    /**
     * 发送通知给供应商
     */
    private function sendNotificationToSuppliers(): void
    {
        // 获取所有启用的供应商ID
        $supplierIds = DB::table('supplier')
            ->where('status', 1)
            ->pluck('id')
            ->toArray();

        if (empty($supplierIds)) {
            return;
        }

        // 发送站内信（异步队列处理）
        // TODO: 实现消息队列发送
        // sendMessage([
        //     'title' => '指导价提醒',
        //     'content' => '指导价已更新，请及时到价格中心查看变动明细',
        //     'receiver_id' => $supplierIds,
        //     'sender_type' => 3,
        //     'category' => 3,
        //     'receiver_type' => 'supp',
        // ]);
    }

    /**
     * 获取导入错误列表
     */
    public function getImportErrorList(string $errorKey): ?array
    {
        $data = Redis::get($errorKey);
        return $data ? json_decode($data, true) : null;
    }
}
