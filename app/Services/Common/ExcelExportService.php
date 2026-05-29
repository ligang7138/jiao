<?php

namespace App\Services\Common;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Excel 导出服务
 */
class ExcelExportService
{
    /**
     * 导出数据到 Excel 文件
     *
     * @param array $headers 表头
     * @param array $data 数据
     * @param string $filename 文件名（不含扩展名）
     * @return string 临时文件路径
     */
    public function export(array $headers, array $data, string $filename = 'export'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置表头
        $columnIndex = 1;
        foreach ($headers as $header) {
            $cell = $sheet->getCellByColumnAndRow($columnIndex, 1);
            $cell->setValue($header);
            $columnIndex++;
        }

        // 设置表头样式
        $lastColumn = $this->getColumnLetter(count($headers));
        $headerRange = "A1:{$lastColumn}1";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 填充数据
        $rowIndex = 2;
        foreach ($data as $row) {
            $columnIndex = 1;
            foreach ($row as $value) {
                $sheet->getCellByColumnAndRow($columnIndex, $rowIndex)
                    ->setValue($value);
                $columnIndex++;
            }
            $rowIndex++;
        }

        // 设置数据区域样式
        if (count($data) > 0) {
            $dataRange = "A2:{$lastColumn}{$rowIndex}";
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // 自动调整列宽
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        // 保存到临时文件
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFile = $tempDir . '/' . $filename . '_' . date('YmdHis') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return $tempFile;
    }

    /**
     * 获取列字母
     */
    private function getColumnLetter(int $columnIndex): string
    {
        $letter = '';
        while ($columnIndex > 0) {
            $columnIndex--;
            $letter = chr(65 + ($columnIndex % 26)) . $letter;
            $columnIndex = intval($columnIndex / 26);
        }
        return $letter;
    }
}
