<?php
namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExcelService {
    public static function downloadXlsx(string $filename, array $headers, array $rows): void {
        $ss = new Spreadsheet();
        $sheet = $ss->getActiveSheet();

        // Cabecera
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:'.Coordinate::stringFromColumnIndex(count($headers)).'1')
              ->getFont()->setBold(true);
        // Datos
        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }

        // Auto width
        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        // Congelar fila de cabecera
        $sheet->freezePane('A2');

        // Descargar
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. $filename .'"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($ss);
        $writer->save('php://output');
        exit;
    }

    public static function downloadCourseReport(string $filename, array $detailHeaders, array $detailRows, array $summaryPairs): void {
    $ss = new Spreadsheet();

    // --- Hoja 1: Detalle ---
    $sheet1 = $ss->getActiveSheet();
    $sheet1->setTitle('Detalle');
    $sheet1->fromArray($detailHeaders, null, 'A1');
    if (!empty($detailRows)) $sheet1->fromArray($detailRows, null, 'A2');
    // Header en negrita + auto ancho + congelar encabezado
    $lastCol1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($detailHeaders));
    $sheet1->getStyle('A1:'.$lastCol1.'1')->getFont()->setBold(true);
    for ($i=1; $i<=count($detailHeaders); $i++) {
        $sheet1->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
    }
    $sheet1->freezePane('A2');

    // --- Hoja 2: Resumen ---
    $sheet2 = $ss->createSheet();
    $sheet2->setTitle('Resumen');
    $sheet2->fromArray([['Métrica','Valor']], null, 'A1');           // cabecera
    if (!empty($summaryPairs)) $sheet2->fromArray($summaryPairs, null, 'A2');
    $sheet2->getStyle('A1:B1')->getFont()->setBold(true);
    $sheet2->getColumnDimension('A')->setAutoSize(true);
    $sheet2->getColumnDimension('B')->setAutoSize(true);

    // Exportar
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($ss);
    $writer->save('php://output');
    exit;
}

}
