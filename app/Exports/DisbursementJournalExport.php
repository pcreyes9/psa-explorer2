<?php

namespace App\Exports;

use App\Models\CashVoucher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DisbursementJournalExport implements
    FromCollection,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
{
    protected string $date;
    

    public function __construct(string $date)
    {
        $this->date = $date;
    }


    /*
    |--------------------------------------------------------------------------
    | DATA STARTS AT ROW 4
    |--------------------------------------------------------------------------
    */

    public function startCell(): string
    {
        return 'A4';
    }


    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    public function collection()
    {
        $vouchers = CashVoucher::with([
            'items' => function ($query) {
                $query->orderBy('id');
            }
        ])
            ->whereDate('date', $this->date)
            ->orderBy('id')
            ->get();

        $rows = collect();

        foreach ($vouchers as $voucher) {

            /*
            |--------------------------------------------------------------------------
            | PAYEE ROW
            |--------------------------------------------------------------------------
            */

            $rows->push([
                $voucher->date->format('n j Y'),
                strtoupper($voucher->pay_to),
                null,
                null,
                null,
                null,
                null,
            ]);


            /*
            |--------------------------------------------------------------------------
            | ITEM ROWS
            |--------------------------------------------------------------------------
            */

            $items = $voucher->items;

            foreach ($items as $index => $item) {

                $isLastItem = $index === ($items->count() - 1);

                $rows->push([
                    null,

                    $item->description,

                    $isLastItem
                        ? $voucher->voucher_no
                        : null,

                    $isLastItem
                        ? $voucher->check_no
                        : null,

                    null,

                    null,

                    $isLastItem
                        ? (float) $voucher->total_amount
                        : null,
                ]);
            }
        }

        return $rows;
    }


    /*
    |--------------------------------------------------------------------------
    | STYLES
    |--------------------------------------------------------------------------
    */

    public function styles(Worksheet $sheet)
    {
        return [

            1 => [
                'font' => [
                    'name' => 'Calibri',
                    'size' => 12,
                    'bold' => true,
                ],
            ],

            3 => [
                'font' => [
                    'name' => 'Calibri',
                    'size' => 12,
                    'bold' => true,
                ],

                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],

                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | COLUMN WIDTHS
    |--------------------------------------------------------------------------
    */

    public function columnWidths(): array
    {
        return [
            'A' => 13.54,
            'B' => 75,
            'C' => 14.54,
            'D' => 14.63,
            'E' => 8.72,
            'F' => 20.72,
            'G' => 20.72,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | EVENTS
    |--------------------------------------------------------------------------
    */

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();


                /*
                |--------------------------------------------------------------------------
                | TITLE
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A1:G1');

                $sheet->setCellValue(
                    'A1',
                    'DISBURSEMENT 2026'
                );


                /*
                |--------------------------------------------------------------------------
                | HEADER
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue('A3', 'DATE');
                $sheet->setCellValue('B3', 'PARTICULARS');
                $sheet->setCellValue('C3', 'VOUCHER #');
                $sheet->setCellValue('D3', 'CHECK #');
                $sheet->setCellValue('E3', '');
                $sheet->setCellValue('F3', 'Check replacement');
                $sheet->setCellValue('G3', 'AMOUNT');


                /*
                |--------------------------------------------------------------------------
                | GENERAL FONT
                |--------------------------------------------------------------------------
                */

                $highestRow = $sheet->getHighestRow();

                $sheet
                    ->getStyle("A1:G{$highestRow}")
                    ->getFont()
                    ->setName('Calibri')
                    ->setSize(12);


                /*
                |--------------------------------------------------------------------------
                | HEADER BORDERS
                |--------------------------------------------------------------------------
                */

                $sheet
                    ->getStyle('A3:G3')
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        Border::BORDER_THIN
                    );


                /*
                |--------------------------------------------------------------------------
                | HEADER ALIGNMENT
                |--------------------------------------------------------------------------
                */

                $sheet
                    ->getStyle('A3:G3')
                    ->getAlignment()
                    ->setHorizontal(
                        Alignment::HORIZONTAL_CENTER
                    )
                    ->setVertical(
                        Alignment::VERTICAL_CENTER
                    );


                /*
                |--------------------------------------------------------------------------
                | AMOUNT FORMAT
                |--------------------------------------------------------------------------
                */

                if ($highestRow >= 4) {

                    $sheet
                        ->getStyle("G4:G{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');

                    $sheet
                        ->getStyle("G4:G{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_RIGHT
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | VOUCHER / CHECK ALIGNMENT
                    |--------------------------------------------------------------------------
                    */

                    $sheet
                        ->getStyle("C4:D{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_RIGHT
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | ROW HEIGHT
                |--------------------------------------------------------------------------
                */

                for ($row = 1; $row <= $highestRow; $row++) {

                    $sheet
                        ->getRowDimension($row)
                        ->setRowHeight(17);
                }

                $sheet
                    ->getRowDimension(1)
                    ->setRowHeight(20);

                $sheet
                    ->getRowDimension(3)
                    ->setRowHeight(20);


                /*
                |--------------------------------------------------------------------------
                | FREEZE HEADER
                |--------------------------------------------------------------------------
                */

                $sheet->freezePane('A4');


                /*
                |--------------------------------------------------------------------------
                | PAGE SETUP
                |--------------------------------------------------------------------------
                */

                $sheet
                    ->getPageSetup()
                    ->setOrientation(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT
                    );

                $sheet
                    ->getPageSetup()
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);


                /*
                |--------------------------------------------------------------------------
                | PAGE MARGINS
                |--------------------------------------------------------------------------
                */

                $sheet
                    ->getPageMargins()
                    ->setTop(0.25)
                    ->setRight(0.25)
                    ->setBottom(0.25)
                    ->setLeft(0.25);
            },
        ];
    }
}