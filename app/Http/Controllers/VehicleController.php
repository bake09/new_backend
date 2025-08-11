<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->where('v.REGISTER_NUMBER', '=', 'LB-DB 1216')
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
            'u.ECC_STATUS',
            's.SPECIFY'
        ])
        ->paginate($request->get('per_page', 10));

        // UTF‑8-Konvertierung der Daten
        $vehicles->getCollection()->transform(function ($item) {
            foreach ($item as $key => $value) {
                if (is_string($value)) {
                    $item->$key = trim(mb_convert_encoding($value, 'UTF-8', 'auto'));
                }
            }
            return $item;
        });

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

    // public function index(Request $request)
    // {
    //     $vehicles = Vehicle::query()
    //         ->from('VEHICLE as v')
    //         ->join('UNIT_FILE as u', 'u.BASIS_NUMBER', '=', 'v.BASIS_NUMBER')
    //         ->join('vPP5L as s', 'u.ECC_STATUS', '=', 's.CAR_STATUS')
    //         ->join('vPP5Q as m', function($join) {
    //             $join->on('v.MODEL_LINE', '=', 'm.MODEL_LINE')
    //                 ->on('v.MAKE_CD', '=', 'm.MAKE_CD');
    //         })
    //         // ->where('u.CAR_CREATION_DATE', '>', '2023-01-01')
    //         ->where('m.MAKE_CD', '>', 'OP')
    //         ->select([
    //             'm.MAKE_CD',
    //             'm.MODEL_LINE',
    //             'm.MOD_LIN_SPECIFY',
    //             'v.BASIS_NUMBER',
    //             'v.REGISTER_NUMBER',
    //             'v.CHASSIS_NUMBER',
    //             'u.ECC_STATUS',
    //             's.SPECIFY'
    //         ])
    //         ->paginate($request->get('per_page', 10));

    //     // Alle Werte rekursiv auf UTF-8 konvertieren (wegen ODBC!)
    //     $vehicles->getCollection()->transform(function ($item) {
    //         foreach ($item as $key => $value) {
    //             if (is_string($value)) {
    //                 $item->$key = mb_convert_encoding($value, 'UTF-8', 'auto');
    //             }
    //         }
    //         return $item;
    //     });

    //     $groupedColumns = [
    //         'MAKE_CD' => Vehicle::distinct('MAKE_CD')->pluck('MAKE_CD'),
    //         // 'model_line' => Vehicle::distinct('MODEL_LINE')->pluck('MODEL_LINE'),
    //     ];

    //     $data = [
    //         'vehicles' => $vehicles,
    //         'grouped_columns' => $groupedColumns,
    //     ];

    //     // return response()->json($vehicles, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    //     return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    // }
}
