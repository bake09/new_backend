<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        ini_set('memory_limit', '2G');
        $basisNumber = '0011YFN9';
        
                $vehicles = Vehicle::select(
                    // [
                    //     'BASIS_NUMBER as BASIS_NUMBER',
                    //     'TRANSACT_DATE as TRANSACT_DATE',
                    //     'CHASSIS_NUMBER as CHASSIS_NUMBER',
                    //     'REGISTER_NUMBER as REGISTER_NUMBER',
                    // ]
                )
                // ->whereRaw('BASIS_NUMBER = ?', [$basisNumber])
                ->orderBy('TRANSACT_DATE', 'desc') // neueste zuerst
                ->limit(100)
                ->get();

        $trimmedVehicles = $vehicles->map(function($vehicle) {
            $vehicleArray = $vehicle->toArray();
            array_walk_recursive($vehicleArray, function(&$value) {
                if (is_string($value)) {
                    $value = trim($value);
                }
            });
            return $vehicleArray;
        });

        return $trimmedVehicles->isNotEmpty() 
            ? response()->json($trimmedVehicles, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE)
            : response()->json(['error' => 'Not found'], 404);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        //
    }
}
