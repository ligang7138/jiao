<?php

namespace App\Services\Goods;

use App\Models\Goods\Goods;
use App\Models\Goods\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * 商品服务层
 */
class GoodsService
{
    /**
     * 获取商品列表
     */
    public function getList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = Goods::with(['category', 'subCategory'])
            ->search($params['keyword'] ?? null)
            ->byCategory($params['cate_id'] ?? null)
            ->byStatus($params['status'] ?? null);

        // 排序
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
                    'goods_sn' => $item->goods_sn,
                    'goods_name' => $item->goods_name,
                    'cate_id' => $item->cate_id,
                    'cate_name' => $item->category?->name,
                    'scate_id' => $item->scate_id,
                    'scate_name' => $item->subCategory?->name,
                    'unit' => $item->unit,
                    'spec' => $item->spec,
                    'discount_rate' => $item->discount_rate,
                    'status' => $item->status,
                    'status_text' => $item->getStatusText(),
                    'slogo' => $item->slogo,
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 创建商品
     */
    public function create(array $data): Goods
    {
        // 生成商品编码
        if (!isset($data['goods_sn'])) {
            $data['goods_sn'] = $this->generateGoodsSn();
        }

        // 设置时间戳
        $data['add_time'] = time();
        $data['update_time'] = time();

        $goods = Goods::create($data);

        return $goods;
    }

    /**
     * 更新商品
     */
    public function update(int $id, array $data): Goods
    {
        $goods = Goods::findOrFail($id);

        // 更新时间戳
        $data['update_time'] = time();

        $goods->update($data);

        return $goods->fresh();
    }

    /**
     * 删除商品
     */
    public function delete(int $id): bool
    {
        $goods = Goods::findOrFail($id);

        return $goods->delete();
    }

    /**
     * 批量删除商品
     */
    public function batchDelete(array $ids): int
    {
        return Goods::whereIn('id', $ids)->delete();
    }

    /**
     * 更改商品状态
     */
    public function changeStatus(int $id, int $status): Goods
    {
        $goods = Goods::findOrFail($id);
        $goods->status = $status;
        $goods->save();

        return $goods;
    }

    /**
     * 获取商品详情
     */
    public function getDetail(int $id): array
    {
        $goods = Goods::with(['category', 'subCategory'])->findOrFail($id);

        return [
            'id' => $goods->id,
            'goods_sn' => $goods->goods_sn,
            'goods_name' => $goods->goods_name,
            'cate_id' => $goods->cate_id,
            'cate_name' => $goods->category?->name,
            'scate_id' => $goods->scate_id,
            'scate_name' => $goods->subCategory?->name,
            'unit' => $goods->unit,
            'spec' => $goods->spec,
            'level' => $goods->level,
            'attr' => $goods->attr,
            'goods_type' => $goods->goods_type,
            'goods_channel' => $goods->goods_channel,
            'discount_rate' => $goods->discount_rate,
            'slogo' => $goods->slogo,
            'image_list' => $goods->image_list,
            'detail_image_list' => $goods->detail_image_list,
            'remark' => $goods->remark,
            'brand' => $goods->brand,
            'place' => $goods->place,
            'expire_date' => $goods->expire_date,
            'status' => $goods->status,
            'status_text' => $goods->getStatusText(),
            'schedule_down_time' => $goods->schedule_down_time,
            'created_at' => $goods->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $goods->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 上传图片
     */
    private function uploadImage($file): string
    {
        $path = $file->store('goods', 'public');
        return $path;
    }

    /**
     * 获取供应商商品列表
     * 注意：goods表中没有supplier_id字段，此方法暂时保留但需根据实际业务调整
     */
    public function getSupplierGoods(int $supplierId): array
    {
        // TODO: 根据实际业务逻辑调整，goods表可能通过其他方式关联供应商
        $goods = Goods::where('status', Goods::STATUS_ON)
            ->orderBy('id', 'desc')
            ->get();

        return $goods->map(function ($item) {
            return [
                'id' => $item->id,
                'goods_sn' => $item->goods_sn,
                'goods_name' => $item->goods_name,
                'unit' => $item->unit,
                'spec' => $item->spec,
                'discount_rate' => $item->discount_rate,
            ];
        })->toArray();
    }

    /**
     * 商品上架
     */
    public function publish(int $id): Goods
    {
        $goods = Goods::findOrFail($id);

        $goods->status = Goods::STATUS_ON;
        $goods->schedule_down_time = 0;
        $goods->update_time = time();
        $goods->save();

        // 记录上下架日志
        $this->logStatusChange($goods, 1, '商品上架');

        return $goods;
    }

    /**
     * 商品下架
     */
    public function unpublish(int $id, int $downType = 1): Goods
    {
        $goods = Goods::findOrFail($id);

        if ($downType === 2) {
            // 预下架：7天后自动下架
            $goods->schedule_down_time = time() + 7 * 86400;
        } else {
            // 立即下架
            $goods->status = Goods::STATUS_OFF;
            $goods->schedule_down_time = 0;
        }

        $goods->update_time = time();
        $goods->save();

        // 记录上下架日志
        $this->logStatusChange($goods, 0, $downType === 2 ? '预下架（7天后自动下架）' : '商品下架');

        return $goods;
    }

    /**
     * 批量上架
     */
    public function batchPublish(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->publish($id);
                $count++;
            } catch (\Exception $e) {
                // 忽略单个失败
            }
        }
        return $count;
    }

    /**
     * 批量下架
     */
    public function batchUnpublish(array $ids): int
    {
        return Goods::whereIn('id', $ids)
            ->update([
                'status' => Goods::STATUS_OFF,
                'schedule_down_time' => 0,
                'update_time' => time()
            ]);
    }

    /**
     * 获取上下架记录
     */
    public function getStatusLog(int $id): array
    {
        // 这里需要配合商品状态日志模型
        // 暂时返回空数组，后续可对接实际日志表
        return [];
    }

    /**
     * 获取历史价格
     */
    public function getHistoryPrice(int $id, array $params = []): array
    {
        $goods = Goods::findOrFail($id);

        // 这里需要配合价格历史模型 goods_jiagewang
        // 暂时返回商品当前折扣率信息
        return [
            'goods_id' => $id,
            'goods_name' => $goods->goods_name,
            'current_discount_rate' => $goods->discount_rate,
            'history' => [],
        ];
    }

    /**
     * 商品导入
     */
    public function import($file): array
    {
        $importService = new \App\Services\Common\ExcelImportService();

        // 读取 Excel 数据
        $data = $importService->import($file->getRealPath());

        // 验证规则
        $rules = [
            '商品名称' => ['required', 'max:255'],
            '规格' => ['max:255'],
            '单位' => ['required', 'max:50'],
            '指导价' => ['numeric'],
        ];

        // 验证数据
        $result = $importService->validate($data, $rules);
        $valid = $result['valid'];
        $errors = $result['errors'];

        $success = 0;
        $failed = count($errors);

        // 导入有效数据
        foreach ($valid as $row) {
            try {
                // 查找或创建分类
                $categoryId = null;
                if (!empty($row['一级分类'])) {
                    $category = Category::where('name', $row['一级分类'])
                        ->where('parent_id', 0)
                        ->first();
                    if ($category) {
                        $categoryId = $category->id;
                    }
                }

                // 创建商品
                Goods::create([
                    'goods_name' => $row['商品名称'],
                    'spec' => $row['规格'] ?? '',
                    'unit' => $row['单位'] ?? '',
                    'discount_rate' => floatval($row['折扣率'] ?? 0),
                    'cate_id' => $categoryId,
                    'status' => Goods::STATUS_OFF, // 默认下架
                    'goods_sn' => $this->generateGoodsSn(),
                    'add_time' => time(),
                    'update_time' => time(),
                ]);

                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => '未知',
                    'errors' => [$e->getMessage()],
                    'data' => $row,
                ];
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => array_slice($errors, 0, 10), // 只返回前10条错误
        ];
    }

    /**
     * 生成商品编号
     */
    private function generateGoodsSn(): string
    {
        return 'G' . date('YmdHis') . rand(1000, 9999);
    }

    /**
     * 商品导出
     */
    public function export(array $params): string
    {
        $query = Goods::with(['category', 'subCategory'])
            ->search($params['keyword'] ?? null)
            ->byCategory($params['cate_id'] ?? null)
            ->byStatus($params['status'] ?? null);

        $goods = $query->orderBy('id', 'desc')->get();

        // 表头
        $headers = [
            '商品编号',
            '商品名称',
            '一级分类',
            '二级分类',
            '规格',
            '单位',
            '折扣率',
            '等级',
            '属性',
            '教师专用',
            '议价商品',
            '上架状态',
            '更新时间',
        ];

        // 数据
        $data = $goods->map(function ($item) {
            return [
                $item->goods_sn ?? $item->id,
                $item->goods_name,
                $item->category?->name ?? '',
                $item->subCategory?->name ?? '',
                $item->spec ?? '',
                $item->unit ?? '',
                $item->discount_rate ?? 0,
                $item->level === 1 ? '普通' : '精品',
                $item->attr === 1 ? '非标品' : ($item->attr === 2 ? '标品' : '特种品'),
                $item->goods_type === 1 ? '是' : '否',
                $item->goods_channel === 1 ? '是' : '否',
                $item->status === 1 ? '上架' : '下架',
                $item->updated_at?->format('Y-m-d H:i:s') ?? '',
            ];
        })->toArray();

        // 使用 ExcelExportService 导出
        $excelService = new \App\Services\Common\ExcelExportService();
        return $excelService->export($headers, $data, 'goods_export');
    }

    /**
     * 获取商品单位列表
     */
    public function getUnits(): array
    {
        return [
            ['id' => 1, 'name' => '斤'],
            ['id' => 2, 'name' => '公斤'],
            ['id' => 3, 'name' => '克'],
            ['id' => 4, 'name' => '个'],
            ['id' => 5, 'name' => '件'],
            ['id' => 6, 'name' => '箱'],
            ['id' => 7, 'name' => '袋'],
            ['id' => 8, 'name' => '瓶'],
            ['id' => 9, 'name' => '包'],
            ['id' => 10, 'name' => '盒'],
        ];
    }

    /**
     * 记录状态变更日志
     */
    private function logStatusChange(Goods $goods, int $newStatus, string $reason): void
    {
        // 这里可以对接日志表
        // 暂时不实现具体逻辑
    }
}