<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CdrPerLoginSheet implements FromCollection, WithTitle, WithHeadings, WithEvents
{
    protected $loginId;
    protected $name;
    protected $cdrRows;

    public function __construct($loginId, $name, Collection $cdrRows)
    {
        $this->loginId = $loginId;
        $this->name    = $name;
        $this->cdrRows = $cdrRows;
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Anzahl der Datenzeilen (ohne Header)
                $rowsCount = count($this->cdrRows);

                // +1 für Header
                $highestRow = $rowsCount + 1;

                // letzte Spalte ermitteln – wir wissen wie viele Spalten in headings
                // Wenn du 17 Spalten hast, dann ist das z. B 'Q'
                // Wir können es dynamisch machen:
                $lastColumnIndex = count($this->headings()); // z. B. 17
                // Excel Spaltenbuchstaben A, B, C, ... 
                // Hier eine kurze Hilfefunktion, um Index zu Buchstaben zu konvertieren
                $lastColumnLetter = $this->columnLetter($lastColumnIndex);

                $rangeAll = "A1:{$lastColumnLetter}{$highestRow}";

                // Setze horizontales Alignment linksbündig für alle Zellen inkl. Header
                $sheet->getStyle($rangeAll)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Optional: Header fett formatieren
                $headerRange = "A1:{$lastColumnLetter}1";
                $sheet->getStyle($headerRange)
                    ->getFont()
                    ->setBold(true);
            }
        ];
    }

    /**
     * Hilfsfunktion: wandelt eine Spaltenindex-Nummer (1-basiert) in Excel-Buchstaben
     * z. B. 1 -> A, 2 -> B, ..., 27 -> AA, etc.
     */
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
