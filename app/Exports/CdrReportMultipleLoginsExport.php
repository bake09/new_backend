<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CdrReportMultipleLoginsExport implements WithMultipleSheets
{
    use Exportable;

    protected $loginsData;

    public function __construct(array $loginsData)
    {
        $this->loginsData = $loginsData;
    }

    public function sheets(): array
    {
        $sheets = [];

        // 1) Logins alphabetisch nach 'name' sortieren
        $sortedLogins = collect($this->loginsData)->sortBy(fn($info) => $info['name']);

        // 2) Sheets erzeugen
        foreach ($sortedLogins as $loginId => $info) {
            $sheets[] = new CdrPerLoginSheetWithHeader(
                $loginId,
                $info['name'],
                collect($info['cdrRows']),
                $info['gesamt'],
                $info['incoming'],
                $info['outgoing'],
                $info['intern'],
                $info['extern'],
                $info['startDate'],
                $info['endDate']
            );
        }

        return $sheets;
    }
}
