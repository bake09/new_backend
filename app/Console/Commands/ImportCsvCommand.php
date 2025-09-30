<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCsvCommand extends Command
{
    protected $signature = 'csv:import {table} {filename} 
        {--delimiter=, : Feldtrennzeichen} 
        {--enclosure=" : Text-Umrandung} 
        {--ignore-header=1 : Anzahl Zeilen am Anfang, die ignoriert werden sollen}';

    protected $description = 'Importiert eine CSV-Datei speicherfreundlich und protokolliert fehlerhafte Zeilen.';

    public function handle()
    {
        $table = $this->argument('table');
        $filename = $this->argument('filename');
        $delimiter = $this->option('delimiter');
        $enclosure = $this->option('enclosure');
        $ignoreHeader = (int)$this->option('ignore-header');

        $path = storage_path("app/public/{$filename}");

        if (!file_exists($path)) {
            $this->error("Datei {$path} nicht gefunden!");
            return Command::FAILURE;
        }
        
        // Tabelle leeren
        DB::table($table)->truncate();

        $this->info("Import starte...");

        $tmpTable = $table . '_tmp';
        $errorTable = $table . '_errors';

        // 1️⃣ Temporäre Tabelle (alles VARCHAR/TEXT)
        DB::statement("DROP TABLE IF EXISTS $tmpTable");
        DB::statement("CREATE TEMPORARY TABLE $tmpTable (
            id VARCHAR(50), callid VARCHAR(50), callstepid VARCHAR(50),
            channelname TEXT, cdraccountid VARCHAR(50), calleraccountid VARCHAR(50),
            callercallerid VARCHAR(255), calledaccountid VARCHAR(50), calledcallerid VARCHAR(255),
            serviceid VARCHAR(50), starttime VARCHAR(50), ringingtime VARCHAR(50),
            linktime VARCHAR(50), callresulttime VARCHAR(50), callresult VARCHAR(255),
            callresultcausedby VARCHAR(50), lineid VARCHAR(50), linename VARCHAR(255),
            callbacknumber TEXT, answeredelswhere VARCHAR(255),
            incoming VARCHAR(10), answered VARCHAR(10), hasvoicemail VARCHAR(10),
            hasmonitor VARCHAR(10), hasfax VARCHAR(10), deleted VARCHAR(10),
            privatecall VARCHAR(10), callbacknumberextern VARCHAR(10), summarystep VARCHAR(10),
            duration VARCHAR(50), login VARCHAR(50)
        )");

        // 2️⃣ Error-Tabelle erstellen, falls nicht existiert
        DB::statement("CREATE TABLE IF NOT EXISTS $errorTable LIKE $table");

        // 3️⃣ CSV in TEMP-Tabelle laden
        $sql = sprintf(
            "LOAD DATA LOCAL INFILE '%s' 
            INTO TABLE %s
            FIELDS TERMINATED BY '%s' 
            ENCLOSED BY '%s'
            LINES TERMINATED BY '\\n'
            IGNORE %d LINES",
            addslashes($path),
            $tmpTable,
            $delimiter,
            $enclosure,
            $ignoreHeader
        );

        try {
            DB::statement($sql);
        } catch (\Throwable $e) {
            $this->error("Fehler beim Laden der CSV: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("CSV geladen, konvertiere und verschiebe Daten...");

        // 4️⃣ Korrekte Zeilen in finale Tabelle
        $insertSql = "
            INSERT INTO $table
            SELECT
                NULLIF(TRIM(id),'') AS id,
                NULLIF(TRIM(callid),'') AS callid,
                NULLIF(TRIM(callstepid),'') AS callstepid,
                channelname,
                NULLIF(TRIM(cdraccountid),'') AS cdraccountid,
                calleraccountid,
                callercallerid,
                NULLIF(TRIM(calledaccountid),'') AS calledaccountid,
                calledcallerid,
                NULLIF(TRIM(serviceid),'') AS serviceid,
                STR_TO_DATE(NULLIF(TRIM(starttime),''), '%d.%m.%Y %H:%i:%s') AS starttime,
                STR_TO_DATE(NULLIF(TRIM(ringingtime),''), '%d.%m.%Y %H:%i:%s') AS ringingtime,
                STR_TO_DATE(NULLIF(TRIM(linktime),''), '%d.%m.%Y %H:%i:%s') AS linktime,
                STR_TO_DATE(NULLIF(TRIM(callresulttime),''), '%d.%m.%Y %H:%i:%s') AS callresulttime,
                callresult,
                NULLIF(TRIM(callresultcausedby),'') AS callresultcausedby,
                NULLIF(TRIM(lineid),'') AS lineid,
                linename,
                callbacknumber,
                answeredelswhere,
                IF(LOWER(TRIM(incoming))='true',1,0),
                IF(LOWER(TRIM(answered))='true',1,0),
                IF(LOWER(TRIM(hasvoicemail))='true',1,0),
                IF(LOWER(TRIM(hasmonitor))='true',1,0),
                IF(LOWER(TRIM(hasfax))='true',1,0),
                IF(LOWER(TRIM(deleted))='true',1,0),
                IF(LOWER(TRIM(privatecall))='true',1,0),
                IF(LOWER(TRIM(callbacknumberextern))='true',1,0),
                IF(LOWER(TRIM(summarystep))='true',1,0),
                NULLIF(TRIM(duration),'') AS duration,
                NULLIF(TRIM(login),'') AS login
            FROM $tmpTable
            WHERE STR_TO_DATE(NULLIF(TRIM(starttime),''), '%d.%m.%Y %H:%i:%s') IS NOT NULL
        ";

        DB::statement($insertSql);

        // 5️⃣ Fehlerhafte Zeilen in Error-Tabelle verschieben
        $errorSql = "
            INSERT INTO $errorTable
            SELECT * FROM $tmpTable
            WHERE STR_TO_DATE(NULLIF(TRIM(starttime),''), '%d.%m.%Y %H:%i:%s') IS NULL
            OR
            (NULLIF(TRIM(id),'') IS NULL AND NULLIF(TRIM(callid),'') IS NULL)
        ";

        DB::statement($errorSql);

        $this->info("Import abgeschlossen. Fehlerhafte Zeilen wurden in '$errorTable' gespeichert.");

        return Command::SUCCESS;
    }
}

// SET GLOBAL local_infile = 1; ind der phpmyadmin GUI!

// Command: php artisan csv:import cdrs cdr.csv --delimiter=";" --ignore-header=1