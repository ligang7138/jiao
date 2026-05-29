<?php

namespace App\Services\Admin;

use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class SystemManagementService
{
    public function departmentOptions(): array
    {
        $table = $this->resolveTable([config('legacy.auth.department_table', 'department'), 'department', 'departments']);
        if (!$table) {
            return [];
        }

        return DB::table($table)
            ->select('id', 'name')
            ->when(Schema::hasColumn($table, 'status'), fn ($query) => $query->where('status', 1))
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => ['id' => (int) $row->id, 'name' => $row->name])
            ->all();
    }

    public function roleOptions(array $params = []): array
    {
        $table = $this->postTable();

        return DB::table($table)
            ->select('id', 'name', 'department_id', 'status')
            ->when(isset($params['department_id']) && $params['department_id'] !== '', fn ($query) => $query->where('department_id', (int) $params['department_id']))
            ->when(Schema::hasColumn($table, 'status'), fn ($query) => $query->where('status', 1))
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'department_id' => isset($row->department_id) ? (int) $row->department_id : null,
                'status' => isset($row->status) ? (int) $row->status : 1,
            ])
            ->all();
    }

    public function roleList(array $params): array
    {
        $table = $this->postTable();
        $departmentTable = $this->resolveTable([config('legacy.auth.department_table', 'department'), 'department', 'departments']);
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, (int) ($params['page_size'] ?? 20));

        $query = DB::table($table . ' as p')
            ->select('p.*')
            ->when($departmentTable, function ($query) use ($departmentTable) {
                $query->leftJoin($departmentTable . ' as d', 'p.department_id', '=', 'd.id')
                    ->addSelect('d.name as department_name');
            })
            ->when(!empty($params['name']), fn ($query) => $query->where('p.name', 'like', '%' . $params['name'] . '%'))
            ->when(isset($params['department_id']) && $params['department_id'] !== '', fn ($query) => $query->where('p.department_id', (int) $params['department_id']))
            ->when(isset($params['status']) && $params['status'] !== '', fn ($query) => $query->where('p.status', (int) $params['status']));

        $total = (clone $query)->count();
        $rows = $query->orderByDesc('p.id')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->map(fn ($row) => $this->formatRole($row))
            ->all();

        return $this->pagePayload($rows, $total, $page, $pageSize, [
            'departments' => $this->departmentOptions(),
        ]);
    }

    public function roleDetail(int $id): ?array
    {
        $table = $this->postTable();
        $row = DB::table($table)->where('id', $id)->first();

        return $row ? $this->formatRole($row) : null;
    }

    public function createRole(array $data, ?object $operator): int
    {
        $table = $this->postTable();
        $exists = DB::table($table)->where('name', $data['name'])->exists();
        if ($exists) {
            throw new RuntimeException('岗位已存在，新增失败', 40001);
        }

        $payload = $this->filterColumns($table, [
            'name' => $data['name'],
            'department_id' => (int) $data['department_id'],
            'remark' => $data['remark'] ?? '',
            'status' => (int) ($data['status'] ?? 0),
            'add_user' => $operator->name ?? $operator->username ?? '',
            'add_time' => time(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table($table)->insertGetId($payload);
    }

    public function updateRole(int $id, array $data): void
    {
        $table = $this->postTable();
        $payload = $this->filterColumns($table, [
            'name' => $data['name'],
            'department_id' => (int) $data['department_id'],
            'remark' => $data['remark'] ?? '',
            'status' => (int) ($data['status'] ?? 0),
            'updated_at' => now(),
        ]);

        DB::table($table)->where('id', $id)->update($payload);
    }

    public function updateRoleStatus(int $id, int $status): void
    {
        DB::table($this->postTable())->where('id', $id)->update(['status' => $status]);
    }

    public function rolePermission(int $id): array
    {
        $role = DB::table($this->postTable())->where('id', $id)->first();
        if (!$role) {
            throw new RuntimeException('记录不存在', 40001);
        }

        return [
            'id' => $id,
            'checked_ids' => $this->parseIds($role->privilege ?? ''),
            'tree' => $this->permissionTree(true),
        ];
    }

    public function updateRolePermission(int $id, array|string $ids): void
    {
        $value = is_array($ids)
            ? implode(',', array_values(array_unique(array_map('intval', $ids))))
            : trim($ids);

        DB::table($this->postTable())->where('id', $id)->update(['privilege' => $value]);
    }

    public function userList(array $params): array
    {
        $table = $this->userTable();
        $departmentTable = $this->resolveTable([config('legacy.auth.department_table', 'department'), 'department', 'departments']);
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, (int) ($params['page_size'] ?? 20));

        $query = DB::table($table . ' as u')
            ->select('u.*')
            ->when($departmentTable, function ($query) use ($departmentTable) {
                $query->leftJoin($departmentTable . ' as d', 'u.department_id', '=', 'd.id')
                    ->addSelect('d.name as department_name');
            })
            ->when(!empty($params['username']), fn ($query) => $query->where('u.username', 'like', '%' . $params['username'] . '%'))
            ->when(!empty($params['name']), fn ($query) => $query->where('u.name', 'like', '%' . $params['name'] . '%'))
            ->when(isset($params['department_id']) && $params['department_id'] !== '', fn ($query) => $query->where('u.department_id', (int) $params['department_id']))
            ->when(isset($params['status']) && $params['status'] !== '', fn ($query) => $query->where('u.status', (int) $params['status']));

        $total = (clone $query)->count();
        $rows = $query->orderByDesc('u.id')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->map(fn ($row) => $this->formatUser($row, $table))
            ->all();

        return $this->pagePayload($rows, $total, $page, $pageSize, [
            'departments' => $this->departmentOptions(),
            'roles' => $this->roleOptions(),
        ]);
    }

    public function userDetail(int $id): ?array
    {
        $table = $this->userTable();
        $row = DB::table($table)->where('id', $id)->first();

        return $row ? $this->formatUser($row, $table) : null;
    }

    public function createUser(array $data): int
    {
        $userTable = $this->userTable();
        $ssoTable = $this->resolveTable(['sso_user']);
        $identityType = 1;

        if ($ssoTable && DB::table($ssoTable)->where('username', $data['username'])->where('identity_type', $identityType)->exists()) {
            throw new RuntimeException('用户账号已存在，添加失败', 40001);
        }
        if (DB::table($userTable)->where('username', $data['username'])->exists()) {
            throw new RuntimeException('用户账号已存在，添加失败', 40001);
        }

        return DB::transaction(function () use ($data, $userTable, $ssoTable, $identityType) {
            $salt = AuthHelper::generateSalt();
            $password = AuthHelper::encryptLegacyPassword(AuthHelper::DEFAULT_PASSWORD, $salt);
            $ssoUserId = null;

            if ($ssoTable) {
                $ssoPayload = $this->filterColumns($ssoTable, [
                    'username' => $data['username'],
                    'password' => $password,
                    'salt' => $salt,
                    'name' => $data['name'],
                    'mobile' => $data['mobile'],
                    'remark' => $data['remark'] ?? '',
                    'status' => (int) $data['status'],
                    'add_time' => time(),
                    'identity_type' => $identityType,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $ssoUserId = (int) DB::table($ssoTable)->insertGetId($ssoPayload);
            }

            $payload = $this->filterColumns($userTable, [
                'username' => $data['username'],
                'password' => $this->passwordForUserTable($userTable, AuthHelper::DEFAULT_PASSWORD, $salt),
                'salt' => Schema::hasColumn($userTable, 'salt') ? $salt : '',
                'name' => $data['name'],
                'department_id' => (int) $data['department_id'],
                'mobile' => $data['mobile'],
                'remark' => $data['remark'] ?? '',
                'status' => (int) $data['status'],
                'add_time' => time(),
                'sso_user_id' => $ssoUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return (int) DB::table($userTable)->insertGetId($payload);
        });
    }

    public function updateUser(int $id, array $data): void
    {
        $table = $this->userTable();
        $payload = $this->filterColumns($table, [
            'name' => $data['name'],
            'department_id' => (int) $data['department_id'],
            'mobile' => $data['mobile'],
            'remark' => $data['remark'] ?? '',
            'updated_at' => now(),
        ]);

        DB::table($table)->where('id', $id)->update($payload);
    }

    public function updateUserStatus(int $id, int $status): void
    {
        $table = $this->userTable();
        $user = DB::table($table)->where('id', $id)->first();
        if (!$user) {
            throw new RuntimeException('记录不存在', 40001);
        }

        DB::transaction(function () use ($table, $user, $id, $status) {
            DB::table($table)->where('id', $id)->update($this->filterColumns($table, [
                'status' => $status,
                'stop_time' => time(),
                'updated_at' => now(),
            ]));

            $ssoTable = $this->resolveTable(['sso_user']);
            if ($ssoTable && !empty($user->sso_user_id)) {
                DB::table($ssoTable)->where('id', $user->sso_user_id)->update($this->filterColumns($ssoTable, [
                    'status' => $status,
                    'update_time' => time(),
                    'updated_at' => now(),
                ]));
            }
        });
    }

    public function resetUserPassword(int $id): void
    {
        $table = $this->userTable();
        $user = DB::table($table)->where('id', $id)->first();
        if (!$user) {
            throw new RuntimeException('记录不存在', 40001);
        }

        DB::transaction(function () use ($table, $user, $id) {
            $salt = AuthHelper::generateSalt();
            $password = AuthHelper::encryptLegacyPassword(AuthHelper::DEFAULT_PASSWORD, $salt);

            $ssoTable = $this->resolveTable(['sso_user']);
            if ($ssoTable && !empty($user->sso_user_id)) {
                DB::table($ssoTable)->where('id', $user->sso_user_id)->update($this->filterColumns($ssoTable, [
                    'password' => $password,
                    'salt' => $salt,
                    'updated_at' => now(),
                ]));
            }

            DB::table($table)->where('id', $id)->update($this->filterColumns($table, [
                'password' => $this->passwordForUserTable($table, AuthHelper::DEFAULT_PASSWORD, $salt),
                'salt' => $salt,
                'updated_at' => now(),
            ]));
        });
    }

    public function userPrivilege(int $id, array $params = []): array
    {
        $user = $this->userDetail($id);
        if (!$user) {
            throw new RuntimeException('记录不存在', 40001);
        }

        $roles = $this->roleList([
            'page' => $params['page'] ?? 1,
            'page_size' => $params['page_size'] ?? 1000,
            'department_id' => $params['department_id'] ?? '',
            'name' => $params['name'] ?? '',
        ]);

        return [
            'id' => $id,
            'checked_ids' => $user['post_ids'],
            'roles' => $roles['list'],
            'total' => $roles['total'],
        ];
    }

    public function updateUserPrivilege(int $id, array|string $postIds): void
    {
        $table = $this->userTable();
        $value = is_array($postIds)
            ? implode(',', array_values(array_unique(array_map('intval', $postIds))))
            : trim($postIds);

        if (Schema::hasColumn($table, 'post')) {
            DB::table($table)->where('id', $id)->update(['post' => $value]);
            return;
        }

        if (Schema::hasTable('admin_user_posts')) {
            DB::table('admin_user_posts')->where('user_id', $id)->delete();
            $rows = array_map(fn ($postId) => ['user_id' => $id, 'post_id' => $postId], $this->parseIds($value));
            if ($rows) {
                DB::table('admin_user_posts')->insert($rows);
            }
        }
    }

    public function permissionList(array $params): array
    {
        $table = $this->menuTable();
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, (int) ($params['page_size'] ?? 20));

        $query = DB::table($table)
            ->when(!empty($params['module']), fn ($query) => $query->where('module', $params['module']))
            ->when(!empty($params['func']), fn ($query) => $query->where('func', 'like', '%' . $params['func'] . '%'))
            ->when(!empty($params['privilege']), fn ($query) => $query->where('privilege', 'like', '%' . $params['privilege'] . '%'))
            ->when(isset($params['level']) && $params['level'] !== '', fn ($query) => $query->where('level', (int) $params['level']))
            ->when(isset($params['status']) && $params['status'] !== '', fn ($query) => $query->where('status', (int) $params['status']));

        $total = (clone $query)->count();
        $rows = $query->orderBy('sort')
            ->orderBy('id')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->map(fn ($row) => $this->formatPermission($row))
            ->all();

        return $this->pagePayload($rows, $total, $page, $pageSize, [
            'modules' => $this->permissionModules(),
        ]);
    }

    public function permissionTree(bool $onlyActive = false): array
    {
        $table = $this->menuTable();
        $rows = DB::table($table)
            ->when($onlyActive, fn ($query) => $query->where('status', 1))
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => $this->formatPermission($row))
            ->all();

        return $this->buildTree($rows);
    }

    public function permissionModules(): array
    {
        return DB::table($this->menuTable())
            ->where('pid', 0)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => $this->formatPermission($row))
            ->all();
    }

    public function permissionControls(int $moduleId): array
    {
        return DB::table($this->menuTable())
            ->select('id', 'func')
            ->where('pid', $moduleId)
            ->where('status', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => ['id' => (int) $row->id, 'func' => $row->func])
            ->all();
    }

    public function createPermission(array $data): int
    {
        $table = $this->menuTable();
        $moduleId = (int) ($data['module'] ?? 0);
        $funcId = (int) ($data['func'] ?? 0);
        $privilege = trim($data['privilege']);
        $path = trim($data['path'] ?? '');
        $status = (int) ($data['status'] ?? 0);

        if ($moduleId === 0 && $funcId === 0) {
            if (DB::table($table)->where('module', $privilege)->where('pid', 0)->exists()) {
                throw new RuntimeException('权限已存在，新增失败', 40001);
            }
            return (int) DB::table($table)->insertGetId($this->filterColumns($table, [
                'module' => $privilege,
                'func' => '',
                'privilege' => '',
                'path' => '',
                'level' => 1,
                'status' => $status,
                'pid' => 0,
            ]));
        }

        if ($funcId === 0) {
            $module = DB::table($table)->where('id', $moduleId)->where('pid', 0)->first();
            if (!$module || DB::table($table)->where('path', $path)->exists()) {
                throw new RuntimeException('权限已存在，新增失败', 40001);
            }

            return (int) DB::table($table)->insertGetId($this->filterColumns($table, [
                'module' => $module->module,
                'func' => $privilege,
                'privilege' => $privilege,
                'path' => $path,
                'level' => 2,
                'status' => $status,
                'pid' => $module->id,
            ]));
        }

        $module = DB::table($table)->where('id', $moduleId)->first();
        $func = DB::table($table)->where('id', $funcId)->first();
        if (!$module || !$func || DB::table($table)->where('path', $path)->exists()) {
            throw new RuntimeException('权限已存在，新增失败', 40001);
        }

        return (int) DB::table($table)->insertGetId($this->filterColumns($table, [
            'module' => $module->module,
            'func' => $func->func,
            'privilege' => $privilege,
            'path' => $path,
            'level' => 3,
            'status' => $status,
            'pid' => $func->id,
        ]));
    }

    public function updatePermission(int $id, array $data): void
    {
        DB::table($this->menuTable())->where('id', $id)->update([
            'privilege' => $data['privilege'],
            'path' => $data['path'],
            'status' => (int) $data['status'],
        ]);
    }

    public function updatePermissionStatus(int $id, int $status): void
    {
        DB::table($this->menuTable())->where('id', $id)->update(['status' => $status]);
    }

    public function permissionDetail(int $id): ?array
    {
        $row = DB::table($this->menuTable())->where('id', $id)->first();

        return $row ? $this->formatPermission($row) : null;
    }

    public function logList(array $params): array
    {
        $table = $this->resolveTable(['system_log']);
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, (int) ($params['page_size'] ?? 20));

        if (!$table) {
            return $this->pagePayload([], 0, $page, $pageSize);
        }

        $startDate = $params['start_date'] ?? date('Y-m-d 00:00:00');
        $endDate = $params['end_date'] ?? date('Y-m-d 23:59:59');
        $query = $this->logQuery($table, [...$params, 'start_date' => $startDate, 'end_date' => $endDate]);
        $total = (clone $query)->count();
        $rows = $query->orderByDesc('id')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->map(fn ($row) => $this->formatLog($row))
            ->all();

        return $this->pagePayload($rows, $total, $page, $pageSize, [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function logDetail(int $id): ?array
    {
        $table = $this->resolveTable(['system_log']);
        if (!$table) {
            return null;
        }

        $row = DB::table($table)->where('id', $id)->first();

        return $row ? $this->formatLog($row, true) : null;
    }

    public function logExportRows(array $params): array
    {
        $table = $this->resolveTable(['system_log']);
        if (!$table) {
            return [];
        }

        return $this->logQuery($table, $params)
            ->orderByDesc('id')
            ->limit(50000)
            ->get()
            ->map(fn ($row) => $this->formatLog($row, true))
            ->all();
    }

    private function logQuery(string $table, array $params)
    {
        return DB::table($table)
            ->when(!empty($params['start_date']), fn ($query) => $query->where('add_time', '>=', strtotime($params['start_date'])))
            ->when(!empty($params['end_date']), fn ($query) => $query->where('add_time', '<=', strtotime($params['end_date'])))
            ->when(!empty($params['username']), fn ($query) => $query->where('username', $params['username']))
            ->when(!empty($params['add_user']), fn ($query) => $query->where('add_user', $params['add_user']))
            ->when(!empty($params['module']), fn ($query) => $query->where('module', $params['module']))
            ->when(!empty($params['method']), fn ($query) => $query->where('method', $params['method']));
    }

    private function formatRole(object $row): array
    {
        return [
            'id' => (int) $row->id,
            'name' => $row->name ?? '',
            'department_id' => isset($row->department_id) ? (int) $row->department_id : null,
            'department_name' => $row->department_name ?? '',
            'remark' => $row->remark ?? '',
            'status' => isset($row->status) ? (int) $row->status : 0,
            'privilege' => $row->privilege ?? '',
            'add_time' => $this->formatTime($row->add_time ?? $row->created_at ?? null, 'Y-m-d H:i'),
        ];
    }

    private function formatUser(object $row, string $table): array
    {
        $postIds = $this->userPostIds($row, $table);
        $roleNames = $this->roleNames($postIds);

        return [
            'id' => (int) $row->id,
            'username' => $row->username ?? '',
            'name' => $row->name ?? '',
            'department_id' => isset($row->department_id) ? (int) $row->department_id : null,
            'department_name' => $row->department_name ?? '',
            'mobile' => $row->mobile ?? '',
            'remark' => $row->remark ?? '',
            'status' => isset($row->status) ? (int) $row->status : 0,
            'post' => implode(',', $postIds),
            'post_ids' => $postIds,
            'role_names' => $roleNames,
            'add_time' => $this->formatTime($row->add_time ?? $row->created_at ?? null, 'Y-m-d H:i'),
        ];
    }

    private function formatPermission(object $row): array
    {
        return [
            'id' => (int) $row->id,
            'module' => $row->module ?? '',
            'func' => $row->func ?? '',
            'privilege' => $row->privilege ?? '',
            'path' => $row->path ?? '',
            'level' => isset($row->level) ? (int) $row->level : 0,
            'pid' => isset($row->pid) ? (int) $row->pid : 0,
            'status' => isset($row->status) ? (int) $row->status : 0,
            'sort' => isset($row->sort) ? (int) $row->sort : 0,
        ];
    }

    private function formatLog(object $row, bool $withDetail = false): array
    {
        $data = [
            'id' => (int) $row->id,
            'username' => $row->username ?? '',
            'add_user' => $row->add_user ?? '',
            'module' => $row->module ?? '',
            'method' => $row->method ?? '',
            'add_time' => $this->formatTime($row->add_time ?? null),
        ];

        if ($withDetail) {
            $data['sql'] = $row->sql ?? '';
            $data['param'] = $row->param ?? '';
        }

        return $data;
    }

    private function userPostIds(object $row, string $table): array
    {
        if (Schema::hasColumn($table, 'post')) {
            return $this->parseIds($row->post ?? '');
        }

        if (!Schema::hasTable('admin_user_posts')) {
            return [];
        }

        return DB::table('admin_user_posts')
            ->where('user_id', $row->id)
            ->pluck('post_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function roleNames(array $postIds): array
    {
        if (!$postIds) {
            return [];
        }

        return DB::table($this->postTable())
            ->whereIn('id', $postIds)
            ->pluck('name')
            ->all();
    }

    private function buildTree(array $rows, int $pid = 0): array
    {
        $tree = [];
        foreach ($rows as $row) {
            if ((int) ($row['pid'] ?? 0) !== $pid) {
                continue;
            }
            $children = $this->buildTree($rows, (int) $row['id']);
            if ($children) {
                $row['children'] = $children;
            }
            $tree[] = $row;
        }

        return $tree;
    }

    private function parseIds(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_unique(array_map('intval', $value)));
        }
        if (!is_string($value) || trim($value) === '') {
            return [];
        }
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return array_values(array_unique(array_map('intval', $decoded)));
        }

        return array_values(array_unique(array_filter(array_map('intval', explode(',', $value)))));
    }

    private function pagePayload(array $list, int $total, int $page, int $pageSize, array $extra = []): array
    {
        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
            'total_page' => (int) ceil($total / $pageSize),
            ...$extra,
        ];
    }

    private function filterColumns(string $table, array $data): array
    {
        return array_filter(
            $data,
            fn ($value, $key) => Schema::hasColumn($table, $key),
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function passwordForUserTable(string $table, string $plain, string $salt): string
    {
        return $table === 'admin_users'
            ? AuthHelper::encryptPassword($plain)
            : AuthHelper::encryptLegacyPassword($plain, $salt);
    }

    private function userTable(): string
    {
        return $this->mustResolveTable([config('legacy.auth.user_table', 'user'), 'user', 'admin_users']);
    }

    private function postTable(): string
    {
        return $this->mustResolveTable([config('legacy.auth.post_table', 'post'), 'post', 'posts']);
    }

    private function menuTable(): string
    {
        return $this->mustResolveTable(['system_menu']);
    }

    private function mustResolveTable(array $candidates): string
    {
        $table = $this->resolveTable($candidates);
        if (!$table) {
            throw new RuntimeException('数据表不存在', 40001);
        }

        return $table;
    }

    private function resolveTable(array $candidates): ?string
    {
        foreach (array_values(array_unique(array_filter($candidates))) as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function formatTime(mixed $value, string $format = 'Y-m-d H:i:s'): ?string
    {
        if (!$value) {
            return null;
        }
        if (is_numeric($value)) {
            return date($format, (int) $value);
        }

        return date($format, strtotime((string) $value));
    }
}
