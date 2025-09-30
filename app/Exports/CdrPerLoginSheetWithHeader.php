<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use Maatwebsite\Excel\Concerns\WithCharts;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class CdrPerLoginSheetWithHeader implements FromCollection, WithTitle, WithHeadings, WithEvents, WithCustomStartCell
{
    protected $loginId;
    protected $name;
    protected $cdrRows;
    protected $gesamt;
    protected $incoming;
    protected $outgoing;
    protected $intern;
    protected $extern;
    protected $startDate;
    protected $endDate;
    protected $infoLines;

    public function __construct(
        $loginId,
        $name,
        Collection $cdrRows,
        $gesamt,
        array $incoming,
        array $outgoing,
        array $intern,
        array $extern,
        $startDate,
        $endDate
    ) {
        $this->loginId   = $loginId;
        $this->name      = $name;
        $this->cdrRows   = $cdrRows;
        $this->gesamt    = $gesamt;
        $this->incoming  = $incoming;
        $this->outgoing  = $outgoing;
        $this->intern    = $intern;
        $this->extern    = $extern;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;

        $this->infoLines = [
            "Anrufe gesamt: {$this->gesamt}",
            "Eingehend: {$this->incoming['gesamt']} (angenommen: {$this->incoming['angenommen']}, nicht angenommen: {$this->incoming['nichtangenommen']})",
            "Ausgehend: {$this->outgoing['gesamt']} (angenommen: {$this->outgoing['angenommen']}, nicht angenommen: {$this->outgoing['nichtangenommen']})",
            "Intern gesamt: {$this->intern['gesamt']} (angenommen: {$this->intern['angenommen']}), nicht angenommen: {$this->intern['nichtangenommen']})",
            "Extern gesamt: {$this->extern['gesamt']} (angenommen: {$this->extern['angenommen']}), nicht angenommen: {$this->extern['nichtangenommen']})",
        ];
    }

    public function collection()
    {
        return $this->cdrRows;
    }

    public function headings(): array
    {
        return [
            'id','callid','callercallerid','calledaccountid','calledcallerid',
            'serviceid','starttime','ringingtime','linktime','callresulttime',
            'callresult','callbacknumber','incoming','answered',
            'callbacknumberextern','duration','login'
        ];
    }

    public function title(): string
    {
        $sheetName = substr($this->name, 0, 31);
        return $sheetName ?: "Login_{$this->loginId}";
    }

    public function startCell(): string
    {
        $infoCount = count($this->infoLines);
        // Name (A1), Rufnummer (A2), Infozeilen ab A3..A7, eine leere Zeile, dann Header
        $headerRow = 1 + 1 + 1 + $infoCount + 1; // Header in A9
        return 'A' . $headerRow;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // 1) Name in A1
                $sheet->setCellValue('A1', "Name: {$this->name}");
                // 1) Name in A1
                $sheet->setCellValue('C1', "Rufnummer: {$this->loginId}");
                
                // 2) Rufnummer in A2
                $sheet->setCellValue('D1', "Zeitraum: KW" . Carbon::parse($this->startDate)->format('W') . " (" . Carbon::parse($this->startDate)->format('d.m.Y') . ") bis KW " . Carbon::parse($this->endDate)->format('W') . " (" . Carbon::parse($this->endDate)->format('d.m.Y') . ")");

                // 3) Tabellen-Header vorbereiten
                $headerRowNumber = intval(substr($this->startCell(), 1));
                $lastColumnIndex = count($this->headings());
                $lastColumnLetter = $this->columnLetter($lastColumnIndex);


                // Einigehende GESAMT
                $eingehendeGesamt = $this->cdrRows->filter(function($row) {
                    return isset($row['incoming']) ? $row['incoming'] === "true" : $row->incoming === "true";
                })->count();
                
                $eingehendeInternGesamt = $this->cdrRows->filter(function($row) {
                    return (isset($row['incoming']) ? $row['incoming'] === "true" : $row->incoming === "true") &&
                           (isset($row['callbacknumberextern']) ? $row['callbacknumberextern'] === "false" : $row->callbacknumberextern === "false");
                })->count();
                $eingehendeInternAngenommen = $this->cdrRows->filter(function($row) {
                    return (isset($row['incoming']) ? $row['incoming'] === "true" : $row->incoming === "true") &&
                        (isset($row['answered']) ? $row['answered'] === "true" : $row->answered === "true") &&
                        (isset($row['callbacknumberextern']) ? $row['callbacknumberextern'] === "false" : $row->callbacknumberextern === "false");
                })->count();
                $eingehendeInternNichtAngenommen = $this->cdrRows->filter(function($row) {
                    return (isset($row['incoming']) ? $row['incoming'] === "true" : $row->incoming === "true") &&
                        (isset($row['answered']) ? $row['answered'] === "false" : $row->answered === "false") &&
                        (isset($row['callbacknumberextern']) ? $row['callbacknumberextern'] === "false" : $row->callbacknumberextern === "false");
                })->count();
            
                $eingehendeExternGesamt = $this->cdrRows->filter(function($row) {
                return (isset($row['incoming']) ? $row['incoming'] === "true" : $row->incoming === "true") &&
                        (isset($row['callbacknumberextern']) ? $row['callbacknumberextern'] === "true" : $row->callbacknumberextern === "true");
                })->count();
                $eingehendeExternAngenommen = $this->cdrRows->filter(function($row) {
                    return (isset($row['incoming']) ? $row['incoming'] === "true" : $row->incoming === "true") &&
                        (isset($row['answered']) ? $row['answered'] === "true" : $row->answered === "true") &&
                        (isset($row['callbacknumberextern']) ? $row['callbacknumberextern'] === "true" : $row->callbacknumberextern === "true");
                })->count();
                $eingehendeExternNichtAngenommen = $this->cdrRows->filter(function($row) {
                    return (isset($row['incoming']) ? $row['incoming'] === "true" : $row->incoming === "true") &&
                        (isset($row['answered']) ? $row['answered'] === "false" : $row->answered === "false") &&
                        (isset($row['callbacknumberextern']) ? $row['callbacknumberextern'] === "true" : $row->callbacknumberextern === "true");
                })->count();




                // Ausgehende GESAMT
                $ausgehendeGesamt = $this->cdrRows->filter(function($row) {
                    return isset($row['incoming']) ? $row['incoming'] === "false" : $row->incoming === "false";
                })->count();
                
                $ausgehendeInternGesamt = $this->cdrRows->filter(function($row) {
                    return (isset($row['incoming']) ? $row['incoming'] === "false" : $row->incoming === "false") &&
                           (isset($row['callbacknumberextern']) ? $row['callbacknumberextern'] === "false" : $row->callbacknumberextern === "false");
                })->count();
                $ausgehendeInternAngenommen = $this->cdrRows->filter(function($row) {
                    return (isset($row['incoming']) ? $row['incoming'] === "false" : $row->incoming === "false") &&
                        (isset($row['answered']) ? $row['answered'] === "true" : $row->answered === "true") &&
                        (isset($row['callbacknumberextern']) ? $row['callbacknumberextern'] === "false" : $row->callbacknumberextern === "false");
                })->count();
                $ausgehendeInternNichtAngenommen = $this->cdrRows->filter(function($row) {
                    return (isset($row['incoming']) ? $row['incoming'] === "false" : $row->incoming === "false") &&
                        (isset($row['answered']) ? $row['answered'] === "false" : $row->answered === "false") &&
                        (isset($row['callbacknumberextern']) ? $row['callbacknumberextern'] === "false" : $row->callbacknumberextern === "false");
                })->count();
            
                $ausgehendeExternGesamt = $this->cdrRows->filter(function($row) {
                return (isset($row['incoming']) ? $row['incoming'] === "false" : $row->incoming === "false") &&
                        (isset($row['callbacknumberextern']) ? $row['callbacknumberextern'] === "true" : $row->callbacknumberextern === "true");
                })->count();
                $ausgehendeExternAngenommen = $this->cdrRows->filter(function($row) {
                    return (isset($row['incoming']) ? $row['incoming'] === "false" : $row->incoming === "false") &&
                        (isset($row['answered']) ? $row['answered'] === "true" : $row->answered === "true") &&
                        (isset($row['callbacknumberextern']) ? $row['callbacknumberextern'] === "true" : $row->callbacknumberextern === "true");
                })->count();
                $ausgehendeExternNichtAngenommen = $this->cdrRows->filter(function($row) {
                    return (isset($row['incoming']) ? $row['incoming'] === "false" : $row->incoming === "false") &&
                        (isset($row['answered']) ? $row['answered'] === "false" : $row->answered === "false") &&
                        (isset($row['callbacknumberextern']) ? $row['callbacknumberextern'] === "true" : $row->callbacknumberextern === "true");
                })->count();

                // NEUE KLEINE TABELLE FÜR INFOS
                $sheet->setCellValue('A4', "Anzahl Anrufe GESAMT: {$this->gesamt}");

                $sheet->setCellValue('A5', "Eingehend: {$eingehendeGesamt}");
                $sheet->setCellValue('A6', "davon INTERN : {$eingehendeInternGesamt}");
                $sheet->setCellValue('A7', "davon EXTERN : {$eingehendeExternGesamt}");

                $sheet->setCellValue('B5', "angenommen");
                $sheet->setCellValue('B6', "{$eingehendeInternAngenommen}");
                $sheet->setCellValue('B7', "{$eingehendeExternAngenommen}");

                $sheet->setCellValue('C5', "nicht angenommen");
                $sheet->setCellValue('C6', "{$eingehendeInternNichtAngenommen}");
                $sheet->setCellValue('C7', "{$eingehendeExternNichtAngenommen}");

                $sheet->setCellValue('D5', "Ausgehend: {$ausgehendeGesamt}");
                $sheet->setCellValue('D6', "davon INTERN : {$ausgehendeInternGesamt}");
                $sheet->setCellValue('D7', "davon EXTERN : {$ausgehendeExternGesamt}");

                $sheet->setCellValue('E5', "angenommen");
                $sheet->setCellValue('E6', "{$ausgehendeInternAngenommen}");
                $sheet->setCellValue('E7', "{$ausgehendeExternAngenommen}");

                $sheet->setCellValue('F5', "nicht angenommen");
                $sheet->setCellValue('F6', "{$ausgehendeInternNichtAngenommen}");
                $sheet->setCellValue('F7', "{$ausgehendeExternNichtAngenommen}");


                // 6) FreezePane auf Header
                $sheet->freezePane('A' . 10);

                // AutoFilter auf die Header-Zeile 9 setzen
                // Anzahl der Spalten aus headings()
                $lastCol = $this->columnLetter(count($this->headings()));
                $sheet->setAutoFilter("A9:{$lastCol}9");

                // 7) Feste Spaltenbreiten
                $columns = range('A', $lastColumnLetter);
                foreach ($columns as $col) {
                    $sheet->getColumnDimension($col)->setWidth(20);
                }

                // 8) Alignment für Tabelle
                $highestRow = $headerRowNumber + count($this->cdrRows);
                $sheet->getStyle("A{$headerRowNumber}:{$lastColumnLetter}{$highestRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // 9) Tabellen-Header fett
                $sheet->getStyle("A{$headerRowNumber}:{$lastColumnLetter}{$headerRowNumber}")
                    ->getFont()
                    ->setBold(true);

                // Rahmen für die kleine Tabelle A5:F7
                
                $sheet->mergeCells('A4:F4');
                $sheet->getStyle('A4:F4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A4:F7')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF888888'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF8F8F8'],
                    ],
                ]);
                $sheet->getStyle('B5:B7')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF8F8F8'],
                    ],
                ]);
                // Sehr hellgrün für B5:B7 und E5:E7
                $sheet->getStyle('B5:B7')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDFFFD6');
                $sheet->getStyle('E5:E7')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDFFFD6');

                // Sehr hellrot für C5:C7 und F5:F7
                $sheet->getStyle('C5:C7')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFE3E3');
                $sheet->getStyle('F5:F7')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFE3E3');
                    
                $sheet->getStyle('B5:B7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C5:C7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E5:E7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F5:F7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Dick orange für Außenrahmen A5:C7
                $sheet->getStyle('A5:C7')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FFFF9900'], // Orange
                        ],
                    ],
                ]);

                // Dick violett für Außenrahmen D5:F7
                $sheet->getStyle('D5:F7')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                            'color' => ['argb' => 'FF8000FF'], // Violett
                        ],
                    ],
                ]);

                // A5 orange und fett
                $sheet->getStyle('A5')->getFont()->setBold(true)->getColor()->setARGB('FFFF9900');

                // D5 violett und fett
                $sheet->getStyle('D5')->getFont()->setBold(true)->getColor()->setARGB('FF8000FF');
            }
        ];
    }

    private function columnLetter(int $colIndex): string
    {
        $col = '';
        while ($colIndex > 0) {
            $mod = ($colIndex - 1) % 26;
            $col = chr(65 + $mod) . $col;
            $colIndex = (int)(($colIndex - $mod) / 26);
        }
        return $col;
    }
}
