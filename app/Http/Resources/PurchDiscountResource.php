<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchDiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "DISCOUNT_CD" => $this->DISCOUNT_CD,
            // "DISCOUNT_NUMBER" => $this->DISCOUNT_NUMBER,
            "LINE_NO" => $this->LINE_NO,
            "CHASSIS_NO_MODIF" => $this->CHASSIS_NO_MODIF,
            // "STATE_CODE" => $this->STATE_CODE,
            "TRANSACT_DATE" => Carbon::parse($this->TRANSACT_DATE)->format('Y-m-d'),
            "HANDLER" => $this->HANDLER,
            "VEHICLE_NUMBER" => $this->VEHICLE_NUMBER,
            "DATE" => Carbon::parse($this->DATE)->format('Y-m-d'),
            "AMOUNT" => $this->AMOUNT,
            // "FRAMEWORK_AGREE" => $this->FRAMEWORK_AGREE,
            "STATUS_X" => $this->STATUS_X,
            "VOUCHER_NUMBER_X" => $this->VOUCHER_NUMBER_X,
            // "BATCH_NUMBER" => $this->BATCH_NUMBER,
            // "DEALER_6" => $this->DEALER_6,
            "AOS_DEALER" => $this->AOS_DEALER,
            // "CONV_FLAG" => $this->CONV_FLAG, 
            "UNIQUE_IDENT" => $this->UNIQUE_IDENT
        ];
    }
}
