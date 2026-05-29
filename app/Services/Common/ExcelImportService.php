<?php

namespace App\Services\Common;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Excel 导入服务
 */
class ExcelImportService
{
    /**
     * 从 Excel 文件读取数据
     *
     * @param string $filePath 文件路径
     * @param int $startRow 起始行（默认从第2行开始，跳过表头）
     * @return array 数据数组
     */
    public function import(string $filePath, int $startRow = 2): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // 获取表头
        $headers = [];
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $headers[$col] = $sheet->getCell($col . '1')->getValue();
        }

        // 读取数据
        $data = [];
        for ($row = $startRow; $row <= $highestRow; $row++) {
            $rowData = [];
            $isEmpty = true;

            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $value = $sheet->getCell($col . $row)->getValue();

                // 处理日期类型
                if ($sheet->getCell($col . $row)->isFormula() && Date::isDateTime($sheet->getCell($col . $row))) {
                    $value = Date::excelToDateTimeObject($value)->format('Y-m-d');
                }

                $rowData[$headers[$col] ?? $col] = $value;

                if (!empty($value)) {
                    $isEmpty = false;
                }
            }

            // 跳过空行
            if (!$isEmpty) {
                $data[] = $rowData;
            }
        }

        return $data;
    }

    /**
     * 验证导入数据
     *
     * @param array $data 导入数据
     * @param array $rules 验证规则
     * @return array ['valid' => 有效数据, 'errors' => 错误信息]
     */
    public function validate(array $data, array $rules): array
    {
        $valid = [];
        $errors = [];

        foreach ($data as $index => $row) {
            $rowErrors = [];
            $rowNumber = $index + 2; // Excel 行号

            foreach ($rules as $field => $rule) {
                $value = $row[$field] ?? null;

                // 必填验证
                if (in_array('required', $rule) && empty($value)) {
                    $rowErrors[] = "{$field}不能为空";
                    continue;
                }

                // 其他验证规则
                foreach ($rule as $r) {
                    if ($r === 'required') continue;

                    if (str_starts_with($r, 'max:')) {
                        $max = (int) substr($r, 4);
                        if (mb_strlen($value) > $max) {
                            $rowErrors[] = "{$field}不能超过{$max}个字符";
                        }
                    }

                    if (str_starts_with($r, 'numeric')) {
                        if (!is_numeric($value)) {
                            $rowErrors[] = "{$field}必须是数字";
                        }
                    }

                    if (str_starts_with($r, 'in:')) {
                        $allowed = explode(',', substr($r, 3));
                        if (!in_array($value, $allowed)) {
                            $rowErrors[] = "{$field}值无效";
                        }
                    }
                }
            }

            if (empty($rowErrors)) {
                $valid[] = $row;
            } else {
                $errors[] = [
                    'row' => $rowNumber,
                    'errors' => $rowErrors,
                    'data' => $row,
                ];
            }
        }

        return ['valid' => $valid, 'errors' => $errors];
    }
}
