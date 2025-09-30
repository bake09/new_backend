<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'BASIS_NUMBER'    => $this->BASIS_NUMBER,
            'CHASSIS_NUMBER'  => $this->CHASSIS_NUMBER,
            'ECC_STATUS'      => $this->ECC_STATUS,
            'MAKE_CD'         => $this->MAKE_CD,
            'MODEL_LINE'      => $this->MODEL_LINE,
            'MOD_LIN_SPECIFY' => $this->MOD_LIN_SPECIFY,
            'REGISTER_NUMBER' => $this->REGISTER_NUMBER,
            'SPECIFY'         => $this->SPECIFY,
            'FIRST_REG_DATE'  => Carbon::parse($this->FIRST_REG_DATE)->format('Y-m-d'),

            'has_purch_discounts' => $this->has_purch_discounts,

            // Discounts als Array of Objects
            'purch_discounts' => $this->whenLoaded('purchDiscounts', function () {
                return $this->purchDiscounts->map(function ($discount) {
                    return [
                        'VEHICLE_NUMBER'   => $discount->VEHICLE_NUMBER,
                        'DISCOUNT_CD'   => $discount->DISCOUNT_CD,
                        // 'DATE'   => $discount->DATE,
                        'DATE'   => Carbon::parse($discount->DATE)->format('Y-m-d'),
                        'AMOUNT'   => $discount->AMOUNT,
                        'STATUS_X'   => $discount->STATUS_X,
                        'CHASSIS_NO_MODIF'   => $discount->CHASSIS_NO_MODIF,
                        'VOUCHER_NUMBER_X'   => $discount->VOUCHER_NUMBER_X,
                    ];
                });
            }),
        ];
    }
}
