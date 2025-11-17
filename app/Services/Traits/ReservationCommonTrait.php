<?php

namespace App\Services\Traits;

use App\Models\Reservation;
use App\Models\Reservations;

trait ReservationCommonTrait
{
    /**
     * Ambil semua reservasi (include relasi user & room).
     */
    public function getAll()
    {
        return Reservations::with(['user', 'room'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ambil detail reservasi berdasarkan ID (include relasi user & room).
     */
    public function getById(int $id)
    {
        return Reservations::with(['user', 'room'])->findOrFail($id);
    }
}
