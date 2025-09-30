<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CdrResource extends JsonResource
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
            'id' => $this->id,
            'callid' => $this->callid,
            'callercallerid' => $this->callercallerid,
            'calledaccountid' => $this->calledaccountid,
            'calledcallerid' => $this->calledcallerid,
            'serviceid' => $this->serviceid,
            'starttime' => $this->starttime,
            'ringingtime' => $this->ringingtime,
            'linktime' => $this->linktime,
            'callresulttime' => $this->callresulttime,
            'callresult' => $this->callresult,
            'callbacknumber' => $this->callbacknumber,
            'incoming' => $this->incoming,
            'answered' => $this->answered,
            'callbacknumberextern' => $this->callbacknumberextern,
            'duration' => $this->duration,
            'login' => $this->login,
        ];
    }
}
