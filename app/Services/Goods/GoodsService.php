<?php

namespace App\Services\Goods;

use App\Models\Goods\Category;
use App\Models\Goods\Goods;
use App\Models\Goods\GoodsJiagewang;
use App\Models\Goods\GoodsStatusLog;
use App\Models\Goods\GoodsUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 商品服务层（对齐旧 admin/goods 业务逻辑）
 */
class GoodsService
{
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($params['page_size'] ?? 20)));

        $query = DB::table('goods as g')
            ->leftJoin('goods_jiagewang as gj', 'gj.goods_id', '=', 'g.id')
            ->select('g.*', 'gj.price', 'gj.white_price');

        $goodsSn = trim((string) ($params['goods_sn'] ?? ''));
        if ($goodsSn !== '') {
            $query->where('g.goods_sn', $goodsSn);
        } else {
            if ($goodsName = trim((string) ($params['goods_name'] ?? $params['keyword'] ?? ''))) {
                $query->where('g.goods_name', 'like', "%{$goodsName}%");
            }
            if (!empty($params['cate_id'])) {
                $query->where('g.cate_id', (int) $params['cate_id']);
            }
            if (!empty($params['scate_id'])) {
                $query->where('g.scate_id', (int) $params['scate_id']);
            }
            if (($params['attr'] ?? '') !== '' && $params['attr'] !== null) {
                $query->where('g.attr', (int) $params['attr']);
            }
            if (($params['level'] ?? '') !== '' && $params['level'] !== null) {
                $query->where('g.level', (int) $params['level']);
            }
            if (!empty($params['limit_price'])) {
                $query->whereNull('gj.price');
            }
            if (($params['status'] ?? '') !== '' && $params['status'] !== null) {
                $query->where('g.status', (int) $params['status']);
            }
            if (($params['goods_type'] ?? '') !== '' && $params['goods_type'] !== null) {
                $query->where('g.goods_type', (int) $params['goods_type']);
            }
            if (($params['goods_channel'] ?? '') !== '' && $params['goods_channel'] !== null) {
                $query->where('g.goods_channel', (int) $params['goods_channel']);
            }
            if (!empty($params['source'])) {
                $query->where('g.source', (int) $params['source']);
            }
        }

        $total = (clone $query)->count();
        $rows = $query->orderByDesc('g.id')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        $floatRateCaps = Category::where('pid', 0)->pluck('float_rate_cap', 'id')->all();

        $list = $rows->map(function ($row) use ($floatRateCaps) {
            $item = (array) $row;
            $floatRateCap = $floatRateCaps[$item['cate_id']] ?? 0.13;
            $basePrice = ((int) ($item['goods_channel'] ?? 0) === 1)
                ? (float) ($item['white_price'] ?? 0)
                : (float) ($item['price'] ?? 0);

            $item['limit_price'] = $floatRateCap
                ? $this->calcLimitPrice($basePrice, (float) $floatRateCap)
                : 0;

            return $item;
        })->values()->all();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    public function create(array $data): Goods
    {
        $cateId = (int) ($data['cate_id'] ?? 0);
        $scateId = (int) ($data['scate_id'] ?? 0);
        $attr = (int) ($data['attr'] ?? 1);
        $level = (int) ($data['level'] ?? 1);

        if (!in_array($attr, [1, 2, 3], true)) {
            throw new \InvalidArgumentException('商品属性错误');
        }
        if (!in_array($level, [1, 2], true)) {
            throw new \InvalidArgumentException('商品等级错误');
        }

        $imageList = $data['image_list'] ?? [];
        if (empty($imageList)) {
            throw new \InvalidArgumentException('请上传商品图片！');
        }

        $cate = Category::find($cateId);
        if (!$cate || $cate->pid != 0) {
            throw new \InvalidArgumentException('一级分类错误，请重新输入');
        }

        $scate = Category::find($scateId);
        if (!$scate || $scate->pid != $cateId) {
            throw new \InvalidArgumentException('二级分类错误，请重新输入');
        }

        $user = Auth::user();
        $goodsSn = $this->generateGoodsSn($cateId);
        $imageList = $this->normalizeImageList($imageList);
        $detailImageList = $this->normalizeImageList($data['detail_image_list'] ?? []);
        $logo = $imageList[0] ?? '';

        return DB::transaction(function () use ($data, $cate, $scate, $user, $goodsSn, $imageList, $detailImageList, $logo, $attr, $level) {
            return Goods::create([
                'goods_sn' => $goodsSn,
                'goods_name' => trim((string) $data['goods_name']),
                'logo' => $logo,
                'slogo' => $logo,
                'image_list' => json_encode($imageList, JSON_UNESCAPED_UNICODE),
                'detail_image_list' => json_encode($detailImageList, JSON_UNESCAPED_UNICODE),
                'spec' => trim((string) ($data['spec'] ?? '')),
                'cate_id' => $cate->id,
                'cate_name' => $cate->name,
                'scate_id' => $scate->id,
                'scate_name' => $scate->name,
                'unit' => trim((string) $data['unit']),
                'attr' => $attr,
                'goods_type' => (int) ($data['goods_type'] ?? 0),
                'goods_channel' => (int) ($data['goods_channel'] ?? 0),
                'discount_rate' => 0,
                'level' => $level,
                'brand' => trim((string) ($data['brand'] ?? '')),
                'place' => trim((string) ($data['place'] ?? '')),
                'expire_date' => trim((string) ($data['expire_date'] ?? '')),
                'remark' => trim((string) ($data['remark'] ?? '')),
                'status' => Goods::STATUS_OFF,
                'update_user' => $user?->name ?? '',
                'add_time' => time(),
                'update_time' => time(),
            ]);
        });
    }

    public function update(int $id, array $data): Goods
    {
        $goods = Goods::findOrFail($id);
        $imageList = $this->normalizeImageList($data['image_list'] ?? json_decode($goods->image_list ?: '[]', true));
        $detailImageList = $this->normalizeImageList($data['detail_image_list'] ?? json_decode($goods->detail_image_list ?: '[]', true));
        $logo = $imageList[0] ?? $goods->slogo;
        $user = Auth::user();

        $goods->update([
            'slogo' => $logo,
            'remark' => trim((string) ($data['remark'] ?? $goods->remark)),
            'place' => trim((string) ($data['place'] ?? $goods->place)),
            'goods_type' => (int) ($data['goods_type'] ?? $goods->goods_type),
            'image_list' => json_encode($imageList, JSON_UNESCAPED_UNICODE),
            'detail_image_list' => json_encode($detailImageList, JSON_UNESCAPED_UNICODE),
            'update_user' => $user?->name ?? '',
            'update_time' => time(),
        ]);

        return $goods->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) Goods::findOrFail($id)->delete();
    }

    public function batchDelete(array $ids): int
    {
        return Goods::whereIn('id', $ids)->delete();
    }

    public function changeStatus(int $id, int $status): Goods
    {
        $goods = Goods::findOrFail($id);
        $goods->status = $status;
        $goods->update_time = time();
        $goods->save();

        return $goods;
    }

    public function getDetail(int $id): array
    {
        $goods = Goods::findOrFail($id);

        return [
            'id' => $goods->id,
            'goods_sn' => $goods->goods_sn,
            'goods_name' => $goods->goods_name,
            'cate_id' => $goods->cate_id,
            'cate_name' => $goods->cate_name,
            'scate_id' => $goods->scate_id,
            'scate_name' => $goods->scate_name,
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
            'update_time' => $goods->update_time,
        ];
    }

    public function getSupplierGoods(int $supplierId): array
    {
        return Goods::where('status', Goods::STATUS_ON)
            ->orderByDesc('id')
            ->get(['id', 'goods_sn', 'goods_name', 'unit', 'spec', 'discount_rate'])
            ->map(fn ($item) => $item->toArray())
            ->all();
    }

    public function publish(int $id): Goods
    {
        $goods = Goods::findOrFail($id);

        if ((int) $goods->status !== Goods::STATUS_OFF) {
            throw new \InvalidArgumentException('只有下架状态的商品才能上架');
        }
        if ((float) $goods->discount_rate < 0) {
            throw new \InvalidArgumentException('商品未设置浮动率，上架失败');
        }

        $jiagewang = GoodsJiagewang::where('goods_id', $id)->first();
        if (!$jiagewang) {
            throw new \InvalidArgumentException('商品未上传指导价，上架失败');
        }

        $user = Auth::user();
        $oldStatus = (int) $goods->status;

        return DB::transaction(function () use ($goods, $user, $oldStatus) {
            $goods->update([
                'status' => Goods::STATUS_ON,
                'schedule_down_time' => 0,
                'down_reason' => '',
                'update_time' => time(),
            ]);

            $this->writeStatusLog($goods, 1, $oldStatus, Goods::STATUS_ON, '', 0);

            return $goods->fresh();
        });
    }

    public function unpublish(int $id, int $downType = 1, string $reason = ''): Goods
    {
        if ($reason === '') {
            throw new \InvalidArgumentException('请输入下架原因');
        }
        if (!in_array($downType, [1, 2], true)) {
            throw new \InvalidArgumentException('下架类型错误');
        }

        $goods = Goods::findOrFail($id);
        if ((int) $goods->status !== Goods::STATUS_ON) {
            throw new \InvalidArgumentException('只有上架状态的商品才能下架');
        }

        $user = Auth::user();
        $oldStatus = (int) $goods->status;
        $scheduleDownTime = 0;
        $newStatus = Goods::STATUS_OFF;
        $operateType = 2;
        $downReason = $reason;
        $logNewStatus = Goods::STATUS_OFF;

        if ($downType === 2) {
            $newStatus = Goods::STATUS_ON;
            $operateType = 3;
            $downReason = '';
            $tomorrow = strtotime(date('Y-m-d', strtotime('+1 day')) . ' 00:00:00');
            $scheduleDownTime = strtotime('+7 days', $tomorrow);
            $logNewStatus = 2;
        }

        return DB::transaction(function () use ($goods, $newStatus, $scheduleDownTime, $downReason, $oldStatus, $operateType, $reason, $logNewStatus) {
            $goods->update([
                'status' => $newStatus,
                'schedule_down_time' => $scheduleDownTime,
                'down_reason' => $downReason,
                'update_time' => time(),
            ]);

            $this->writeStatusLog($goods, $operateType, $oldStatus, $logNewStatus, $reason, $scheduleDownTime);

            return $goods->fresh();
        });
    }

    public function batchPublish(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->publish((int) $id);
                $count++;
            } catch (\Exception) {
            }
        }

        return $count;
    }

    public function batchUnpublish(array $ids): int
    {
        return Goods::whereIn('id', $ids)->update([
            'status' => Goods::STATUS_OFF,
            'discount_rate' => 0,
            'schedule_down_time' => 0,
            'update_time' => time(),
        ]);
    }

    public function getStatusLog(int $id): array
    {
        $logs = GoodsStatusLog::where('goods_id', $id)
            ->orderByDesc('operate_time')
            ->get();

        $operateTypeMap = [1 => '上架', 2 => '立即下架', 3 => '预下架'];
        $statusMap = [0 => '下架', 1 => '上架', 2 => '待下架'];

        return $logs->map(function ($log) use ($operateTypeMap, $statusMap) {
            return [
                'id' => $log->id,
                'goods_id' => $log->goods_id,
                'operate_type' => $log->operate_type,
                'operate_type_text' => $operateTypeMap[$log->operate_type] ?? '',
                'old_status' => $log->old_status,
                'old_status_text' => $statusMap[$log->old_status] ?? '',
                'new_status' => $log->new_status,
                'new_status_text' => $statusMap[$log->new_status] ?? '',
                'reason' => $log->reason,
                'operator' => $log->operator,
                'operate_user' => $log->operator,
                'operate_time' => $log->operate_time,
                'operate_time_text' => $log->operate_time ? date('Y-m-d H:i:s', $log->operate_time) : '',
                'schedule_down_time' => $log->schedule_down_time,
                'schedule_down_time_text' => $log->schedule_down_time
                    ? date('Y-m-d H:i:s', $log->schedule_down_time)
                    : '',
            ];
        })->all();
    }

    public function getHistoryPrice(int $id, array $params = []): array
    {
        $goods = Goods::findOrFail($id);

        return [
            'goods_id' => $id,
            'goods_name' => $goods->goods_name,
            'current_discount_rate' => $goods->discount_rate,
            'history' => [],
        ];
    }

    public function import($file): array
    {
        throw new \RuntimeException('商品导入功能待对接旧模板逻辑');
    }

    public function export(array $params): string
    {
        $result = $this->getList(array_merge($params, ['page' => 1, 'page_size' => 100000]));
        $headers = ['商品编号', '商品名称', '一级分类', '二级分类', '规格', '单位', '上架状态', '更新时间'];
        $data = collect($result['list'])->map(function ($item) {
            return [
                $item['goods_sn'] ?? '',
                $item['goods_name'] ?? '',
                $item['cate_name'] ?? '',
                $item['scate_name'] ?? '',
                $item['spec'] ?? '',
                $item['unit'] ?? '',
                ((int) ($item['status'] ?? 0) === 1) ? '上架' : '下架',
                !empty($item['update_time']) ? date('Y-m-d H:i:s', (int) $item['update_time']) : '',
            ];
        })->all();

        $excelService = new \App\Services\Common\ExcelExportService();

        return $excelService->export($headers, $data, 'goods_export');
    }

    public function getUnits(): array
    {
        return app(GoodsUnitService::class)->getActiveUnits();
    }

    private function generateGoodsSn(int $cateId): string
    {
        if ($cateId >= 10) {
            $maxSn = Goods::where('cate_id', '>=', 10)->max('goods_sn');
        } else {
            $maxSn = Goods::where('cate_id', $cateId)->max('goods_sn');
        }

        if ($maxSn) {
            $goodsSn = str_pad((string) ((int) $maxSn + 1), 6, '0', STR_PAD_LEFT);
        } else {
            $goodsSn = str_pad((string) ($cateId + 1), 6, '0', STR_PAD_RIGHT);
        }

        if (Goods::where('goods_sn', $goodsSn)->exists()) {
            $maxSn = Goods::max('goods_sn');
            $goodsSn = str_pad((string) ((int) $maxSn + 1), 6, '0', STR_PAD_LEFT);
        }

        return $goodsSn;
    }

    private function normalizeImageList(array $images): array
    {
        return array_values(array_filter(array_map(function ($img) {
            $img = ltrim(str_replace(config('app.upload_url', ''), '', (string) $img), '/');
            return str_replace('tmp/', 'goods/', $img);
        }, $images)));
    }

    private function calcLimitPrice(float $price, float $discountRate): float
    {
        return round($price * $discountRate + $price, 2);
    }

    private function writeStatusLog(
        Goods $goods,
        int $operateType,
        int $oldStatus,
        int $newStatus,
        string $reason,
        int $scheduleDownTime
    ): void {
        $user = Auth::user();

        GoodsStatusLog::create([
            'goods_id' => $goods->id,
            'goods_sn' => $goods->goods_sn,
            'goods_name' => $goods->goods_name,
            'operator' => $user?->name ?? '',
            'operate_time' => time(),
            'operate_type' => $operateType,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'schedule_down_time' => $scheduleDownTime,
        ]);
    }
}
