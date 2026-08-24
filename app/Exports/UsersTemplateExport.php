<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UsersTemplateExport
{
    public function generate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['Nama', 'Email', 'Role', 'Status', 'No. HP', 'No. Induk', 'Password'];
        foreach ($headers as $index => $header) {
            $sheet->setCellValue(self::colLetter($index + 1) . '1', $header);
        }

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'B45F06']],
            'alignment' => ['horizontal' => 'center'],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Contoh data
        $examples = [
            ['Budi Santoso', 'budi@example.com', 'dosen', 'aktif', '081234567890', 'NIP001', ''],
            ['Ani Wijaya', 'ani@example.com', 'mahasiswa', 'aktif', '081234567891', 'NIM001', ''],
            ['Cakra Dewa', 'cakra@example.com', 'teknisi', 'aktif', '081234567892', 'NIP002', 'custom123'],
        ];

        foreach ($examples as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValue(self::colLetter($colIndex + 1) . ($rowIndex + 2), $value);
            }
        }

        // Auto width
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add data validation for Role
        $validation = $sheet->getCell('C2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setError('Role tidak valid');
        $validation->setFormula1('"admin_jurusan,kepala_labor,kadep,teknisi,dosen,mahasiswa"');
        $sheet->setDataValidation('C2:C1000', $validation);

        // Add data validation for Status
        $validationStatus = $sheet->getCell('D2')->getDataValidation();
        $validationStatus->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validationStatus->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validationStatus->setError('Status tidak valid');
        $validationStatus->setFormula1('"aktif,tidak_aktif"');
        $sheet->setDataValidation('D2:D1000', $validationStatus);

        return $spreadsheet;
    }

    private static function colLetter(int $column): string
    {
        $letter = '';
        while ($column > 0) {
            $column--;
            $letter = chr(65 + ($column % 26)) . $letter;
            $column = intdiv($column, 26);
        }
        return $letter;
    }

    public function download()
    {
        $spreadsheet = $this->generate();
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="template_users.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
