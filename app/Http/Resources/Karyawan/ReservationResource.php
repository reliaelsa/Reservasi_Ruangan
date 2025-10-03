<?php

namespace App\Http\Resources\Karyawan;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'room'       => new RoomResource($this->whenLoaded('room')),
            'user_id'    => $this->user_id,
            'date'       => $this->date,
            'hari'       => $this->hari,
            'start_time' => $this->start_time,
            'end_time'   => $this->end_time,
            'status'     => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
