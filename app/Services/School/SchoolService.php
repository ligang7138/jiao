<?php

namespace App\Services\School;

use App\Models\School\Canteen;
use App\Models\School\School;
use App\Support\BiddingLogHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 学校服务层（对齐旧 school/ajax.php 业务逻辑）
 */
class SchoolService
{
    /**
     * @return string[]
     */
    public function getSchoolPeriods(): array
    {
        $periods = config('legacy.dictionary.school_period', []);

        return array_values($periods);
    }

    public function getDistricts(): array
    {
        if (!Schema::hasTable('school_district')) {
            return [];
        }

        return DB::table('school_district')
            ->orderBy('id')
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
    }

    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($params['page_size'] ?? 20)));

        $query = DB::table('school as s');

        if (!empty($params['school_name'])) {
            $query->where('s.school_name', 'like', '%' . trim($params['school_name']) . '%');
        }

        if (!empty($params['school_sn'])) {
            $query->where('s.school_sn', 'like', '%' . trim($params['school_sn']) . '%');
        }

        if (!empty($params['school_district'])) {
            $query->where('s.school_district', trim($params['school_district']));
        }

        if (isset($params['status']) && $params['status'] !== '' && $params['status'] !== null) {
            $query->where('s.status', (int) $params['status']);
        }

        if (isset($params['keyword']) && $params['keyword'] !== '') {
            $keyword = trim($params['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('s.school_name', 'like', "%{$keyword}%")
                    ->orWhere('s.school_sn', 'like', "%{$keyword}%")
                    ->orWhere('s.school_district', 'like', "%{$keyword}%");
            });
        }

        $biddingStatus = $params['bidding_status'] ?? null;
        if ($biddingStatus === '1' || $biddingStatus === 1) {
            BiddingLogHelper::applySchoolExists($query);
        } elseif ($biddingStatus === '0' || $biddingStatus === 0) {
            BiddingLogHelper::applySchoolNotExists($query);
        }

        $total = (clone $query)->count();

        $rows = $query
            ->select('s.*')
            ->orderByDesc('s.id')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        $schoolIds = $rows->pluck('id')->all();
        $supplierNums = [];

        if (!empty($schoolIds)) {
            $supplierNums = BiddingLogHelper::countSuppliersBySchoolIds($schoolIds);
        }

        $canteenCounts = [];
        if (!empty($schoolIds)) {
            $canteenCounts = DB::table('school_canteen')
                ->select('school_id', DB::raw('count(1) as num'))
                ->whereIn('school_id', $schoolIds)
                ->groupBy('school_id')
                ->pluck('num', 'school_id')
                ->all();
        }

        return [
            'list' => $rows->map(function ($item) use ($supplierNums, $canteenCounts) {
                $school = (new School())->forceFill((array) $item);

                return [
                    'id' => (int) $item->id,
                    'school_sn' => $item->school_sn,
                    'school_name' => $item->school_name,
                    'school_district' => $item->school_district,
                    'school_period' => $item->school_period ?? null,
                    'status' => (int) $item->status,
                    'status_text' => $school->getStatusText(),
                    'supplier_num' => (int) ($supplierNums[$item->id] ?? 0),
                    'canteen_count' => (int) ($canteenCounts[$item->id] ?? 0),
                    'add_time' => $item->add_time ? date('Y-m-d H:i:s', (int) $item->add_time) : null,
                ];
            })->values()->all(),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    public function create(array $data): School
    {
        $schoolName = trim($data['school_name'] ?? '');
        $schoolDistrict = trim($data['school_district'] ?? '');
        $schoolPeriod = trim($data['school_period'] ?? '');
        $status = (int) ($data['status'] ?? School::STATUS_ENABLED);

        $this->assertValidStatus($status);
        $this->assertValidPeriod($schoolPeriod);

        if ($schoolName === '') {
            throw new \InvalidArgumentException('请输入学校名称');
        }

        if ($schoolDistrict === '') {
            throw new \InvalidArgumentException('请选择学区');
        }

        if ($schoolPeriod === '') {
            throw new \InvalidArgumentException('请选择学段');
        }

        if (School::where('school_name', $schoolName)->exists()) {
            throw new \InvalidArgumentException('学校名称已存在，添加失败');
        }

        return DB::transaction(function () use ($schoolName, $schoolDistrict, $schoolPeriod, $status) {
            // 旧库 school 表 username/password/salt 为 NOT NULL，新增学校时尚未创建登录账号
            $school = School::create([
                'username' => '',
                'password' => '',
                'salt' => '',
                'school_name' => $schoolName,
                'school_district' => $schoolDistrict,
                'school_period' => $schoolPeriod,
                'status' => $status,
                'add_time' => time(),
            ]);

            $schoolSn = 10000 + (int) $school->id;
            $school->update(['school_sn' => (string) $schoolSn]);

            return $school->fresh();
        });
    }

    public function update(int $id, array $data): School
    {
        $school = School::find($id);
        if (!$school) {
            throw new \InvalidArgumentException('记录不存在');
        }

        $schoolName = trim($data['school_name'] ?? $school->school_name);
        $schoolDistrict = trim($data['school_district'] ?? $school->school_district);
        $schoolPeriod = trim($data['school_period'] ?? $school->school_period);
        $status = (int) ($data['status'] ?? $school->status);

        $this->assertValidStatus($status);
        $this->assertValidPeriod($schoolPeriod);

        if ($schoolName === '') {
            throw new \InvalidArgumentException('请输入学校名称');
        }

        if ($schoolDistrict === '') {
            throw new \InvalidArgumentException('请选择学区');
        }

        if ($schoolPeriod === '') {
            throw new \InvalidArgumentException('请选择学段');
        }

        return DB::transaction(function () use ($school, $schoolName, $schoolDistrict, $schoolPeriod, $status) {
            $school->update([
                'school_name' => $schoolName,
                'school_district' => $schoolDistrict,
                'school_period' => $schoolPeriod,
                'status' => $status,
                'update_time' => time(),
            ]);

            if ($status !== School::STATUS_ENABLED) {
                $this->clearSchoolUserTokens((int) $school->id);
            }

            return $school->fresh();
        });
    }

    public function setStatus(int $id, int $status): School
    {
        $this->assertValidStatus($status);

        $school = School::find($id);
        if (!$school) {
            throw new \InvalidArgumentException('记录不存在');
        }

        return DB::transaction(function () use ($school, $status) {
            $school->update([
                'status' => $status,
                'update_time' => time(),
            ]);

            if ($status !== School::STATUS_ENABLED) {
                $this->clearSchoolUserTokens((int) $school->id);
            }

            return $school->fresh();
        });
    }

    public function getDetail(int $id): array
    {
        $school = School::find($id);
        if (!$school) {
            throw new \InvalidArgumentException('记录不存在');
        }

        return [
            'id' => $school->id,
            'school_sn' => $school->school_sn,
            'school_name' => $school->school_name,
            'school_district' => $school->school_district,
            'school_period' => $school->school_period,
            'status' => (int) $school->status,
            'status_text' => $school->getStatusText(),
            'bank_no' => $school->bank_no,
            'taxpayer_no' => $school->taxpayer_no,
            'invoice_title' => $school->invoice_title,
            'invoice_phone' => $school->invoice_phone,
            'invoice_address' => $school->invoice_address,
            'add_time' => $school->add_time ? date('Y-m-d H:i:s', (int) $school->add_time) : null,
        ];
    }

    public function getActiveSchools(): array
    {
        return School::query()
            ->where('status', School::STATUS_ENABLED)
            ->orderBy('school_name')
            ->get(['id', 'school_name', 'school_sn'])
            ->map(fn ($item) => [
                'id' => $item->id,
                'school_name' => $item->school_name,
                'school_sn' => $item->school_sn,
            ])
            ->all();
    }

    private function assertValidStatus(int $status): void
    {
        if (!in_array($status, [School::STATUS_ENABLED, School::STATUS_DISABLED], true)) {
            throw new \InvalidArgumentException('状态错误');
        }
    }

    private function assertValidPeriod(string $period): void
    {
        if ($period === '') {
            return;
        }

        if (!in_array($period, $this->getSchoolPeriods(), true)) {
            throw new \InvalidArgumentException('请选择正确的学段');
        }
    }

    private function clearSchoolUserTokens(int $schoolId): void
    {
        if (!Schema::hasTable('school_user') || !Schema::hasTable('sso_user')) {
            return;
        }

        $ssoUserIds = DB::table('school_user')
            ->where('school_id', $schoolId)
            ->pluck('sso_user_id')
            ->filter()
            ->all();

        if (empty($ssoUserIds)) {
            return;
        }

        DB::table('sso_user')
            ->whereIn('id', $ssoUserIds)
            ->update([
                'token' => '',
                'update_time' => time(),
            ]);
    }
}
