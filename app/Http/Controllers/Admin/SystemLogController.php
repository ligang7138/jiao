<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Admin\SystemManagementService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemLogController extends Controller
{
    public function __construct(private readonly SystemManagementService $service)
    {
    }

    public function index(Request $request)
    {
        return ResponseHelper::success($this->service->logList($request->all()));
    }

    public function show($id)
    {
        $log = $this->service->logDetail((int) $id);
        if (!$log) {
            return ResponseHelper::error(40001, '记录不存在');
        }

        return ResponseHelper::success($log);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->service->logExportRows($request->all());
        $filename = 'system-log-' . date('YmdHis') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['操作时间', '登录账号', '用户名称', '功能', '操作内容', 'SQL', '参数']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['add_time'],
                    $row['username'],
                    $row['add_user'],
                    $row['module'],
                    $row['method'],
                    $row['sql'] ?? '',
                    $row['param'] ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
