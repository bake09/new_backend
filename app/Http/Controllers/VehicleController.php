<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Models\PurchDiscount;
use App\Models\PurchDiscType;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\VehicleResource;
use App\Http\Resources\PurchDiscountResource;
use App\Http\Resources\PurchDiscTypeResource;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query()
            ->from('VEHICLE as v')
            ->leftJoin('UNIT_FILE as u', 'v.BASIS_NUMBER', '=', 'u.BASIS_NUMBER')
            ->join('vPP5L as s', 'u.ECC_STATUS', '=', 's.CAR_STATUS')
            ->join('vPP5Q as m', function($join) {
                $join->on('v.MODEL_LINE', '=', 'm.MODEL_LINE')
                    ->on('v.MAKE_CD', '=', 'm.MAKE_CD');
            })
            // ->where('u.CAR_CREATION_DATE', '>', '2023-01-01')
            // ->where('v.REGISTER_NUMBER', '=', 'LB-DB 1216')
            // ->where('v.CHASSIS_NUMBER', '=', 'VXEVJYHV7R7841500')
            ->where('v.FIRST_REG_DATE', '>', '2023-01-01')
            ->with('purchDiscounts')
            ;
        
        // Optional: Filter anwenden, falls gesetzt
        $query->when(
            $request->filled('MAKE_CD'),
            fn ($q) => $q->whereIn('m.MAKE_CD', (array) $request->input('MAKE_CD'))
        );
        $query->when(
            $request->filled('ECC_STATUS'),
            fn ($q) => $q->whereIn('u.ECC_STATUS', (array) $request->input('ECC_STATUS'))
        );
        $query->when(
            $request->filled('SPECIFY'),
            fn ($q) => $q->whereIn('s.SPECIFY', (array) $request->input('SPECIFY'))
        );

        // $query->when($request->filled('model_line'), fn($q) => $q->where('m.MODEL_LINE', $request->model_line));
        // $query->when($request->filled('ecc_status'), fn($q) => $q->where('u.ECC_STATUS', $request->ecc_status));

        // Haupt-Abfrage mit Pagination
        $vehicles = $query->select([
            'm.MAKE_CD',
            'm.MODEL_LINE',
            'm.MOD_LIN_SPECIFY',
            'v.BASIS_NUMBER',
            'v.REGISTER_NUMBER',
            'v.CHASSIS_NUMBER',
            'v.FIRST_REG_DATE',
            'u.ECC_STATUS',
            's.SPECIFY',
        ])
        ->paginate($request->get('per_page', 10))
        ;

        // UTF‑8-Konvertierung der Daten
        $vehicles->getCollection()->transform(function ($item) {
            foreach ($item as $key => $value) {
                if (is_string($value)) {
                    $item->$key = trim(mb_convert_encoding($value, 'UTF-8', 'auto'));
                }
            }
            return $item;
        });

        // Jetzt Resource auf die Collection anwenden
        $vehicles->setCollection(
            VehicleResource::collection($vehicles->getCollection())->collection
        );

        // Filteroptionen für q‑selects
        $groupedColumns = [
            'MAKE_CD'    => Vehicle::distinct('MAKE_CD')->orderBy('MAKE_CD')->pluck('MAKE_CD'),
            'ECC_STATUS' => DB::connection('odbc_intern')->table('UNIT_FILE')->distinct('ECC_STATUS')->pluck('ECC_STATUS')->sortDesc(),
        ];

        return response()->json([
            'vehicles'         => $vehicles,
            'grouped_columns'  => $groupedColumns,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }
    
    // public function purchdiscounts(Request $request)
    // {
    //     // return $request->all();
    //     $purchDiscounts = PurchDiscount::query()
    //         ->orderBy('UNIQUE_IDENT', 'desc')
    //         ->paginate($request->get('params', 10))
    //     ;
        
    //     $purchDiscounts->setCollection(
    //         PurchDiscountResource::collection($purchDiscounts->getCollection())->collection
    //     );

    //     return response()->json($purchDiscounts, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    // }
    public function purchdiscounts(Request $request)
    {
        $purchDiscounts = PurchDiscount::query()
            ->orderBy('UNIQUE_IDENT', 'desc')
            ->paginate($request->get('per_page', 10));

        // Optional: UTF-8-Korrektur, falls nötig
        $purchDiscounts->getCollection()->transform(function ($item) {
            foreach ($item as $key => $value) {
                if (is_string($value)) {
                    $item->$key = mb_convert_encoding($value, 'UTF-8', 'auto');
                }
            }
            return $item;
        });

        $meta = [
            'page' => $purchDiscounts->currentPage(),
            'per_page' => $purchDiscounts->perPage(),
            'total' => $purchDiscounts->total(),
            'last_page' => $purchDiscounts->lastPage(),
            'from' => $purchDiscounts->firstItem(),
            'to' => $purchDiscounts->lastItem(),
            'next_page_url' => $purchDiscounts->nextPageUrl(),
            'prev_page_url' => $purchDiscounts->previousPageUrl(),
        ];

        return response()->json([
            'meta' => $meta,
            'data' => PurchDiscountResource::collection($purchDiscounts->items())
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }

    public function purchdisctypes(Request $request)
    {
        // Erstellen bearbeiten Dracar-Funktion: EDM5
       $purchDiscTypes = PurchDiscType::select([
            'DISCOUNT_CD As Rabattcode',
            'FACTORY_MODEL_CODE As Herstellermodellcode',
            'TRANSACT_DATE As Erstellungsdatum',
            'HANDLER As Ersteller',
            'DISCOUNT_TEXT As Nachlasstext',
            'UNIQUE_IDENT',
        ])
        ->orderBy('Erstellungsdatum', 'desc')
        ->get()
        ;

        return response()->json($purchDiscTypes, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }

    public function doublepurchdisctype(Request $request)
    {
        // Datensatz abfragen
        $doublepurchdisctype = PurchDiscType::select([
            'DISCOUNT_CD',
            'FACTORY_MODEL_CODE',
            'TRANSACT_DATE',
            'HANDLER',
            'DISCOUNT_TEXT',
            'UNIQUE_IDENT',
        ])
        ->whereIn('DISCOUNT_CD', function ($query) {
            $query->select('DISCOUNT_CD')
                ->from('PURCH_DISC_TYPES')
                ->groupBy('DISCOUNT_CD')
                ->havingRaw('COUNT(*) > 1');
        })
        ->paginate($request->get('per_page', 1000000));

        // Resource anwenden
        $doublepurchdisctype->setCollection(
            PurchDiscTypeResource::collection($doublepurchdisctype->getCollection())->collection
        );

        return response()->json( $doublepurchdisctype, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }

    // public function doublepurchdisctype(Request $request)
    // {
    //     // 🛠 Zeichensatz für PDO_ODBC-Verbindung auf UTF-8 setzen
    //     try {
    //         DB::connection('odbc_intern')->getPdo()->exec("SET NAMES 'UTF-8'");
    //     } catch (\Throwable $e) {
    //         // Falls der Treiber SET NAMES nicht kennt → einfach weitermachen
    //     }

    //     // Datensatz abfragen
    //     $doublepurchdisctype = PurchDiscType::select([
    //         'DISCOUNT_CD',
    //         'FACTORY_MODEL_CODE',
    //         'TRANSACT_DATE',
    //         'HANDLER',
    //         'DISCOUNT_TEXT',
    //         'UNIQUE_IDENT',
    //     ])
    //     ->whereIn('DISCOUNT_CD', function ($query) {
    //         $query->select('DISCOUNT_CD')
    //             ->from('PURCH_DISC_TYPES')
    //             ->groupBy('DISCOUNT_CD')
    //             ->havingRaw('COUNT(*) > 1');
    //     })
    //     ->paginate($request->get('per_page', 1000000));

    //     // Resource anwenden
    //     $doublepurchdisctype->setCollection(
    //         PurchDiscTypeResource::collection($doublepurchdisctype->getCollection())->collection
    //     );

    //     // UTF-8 sicherstellen (Konvertierung nur, falls nötig)
    //     $doublepurchdisctype->getCollection()->transform(function ($item) {
    //         foreach ($item as $key => $value) {
    //             if (is_string($value)) {
    //                 // Nur konvertieren, wenn String nicht schon UTF-8 ist
    //                 if (!mb_check_encoding($value, 'UTF-8')) {
    //                     $item->$key = trim(mb_convert_encoding($value, 'UTF-8', 'Windows-1252'));
    //                 } else {
    //                     $item->$key = trim($value);
    //                 }
    //             }
    //         }
    //         return $item;
    //     });

    //     return response()->json(
    //         $doublepurchdisctype,
    //         200,
    //         [],
    //         JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE
    //     );
    // }
}
