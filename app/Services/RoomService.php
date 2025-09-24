<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Rooms;
use Illuminate\Validation\ValidationException;

class RoomService
{
    public function getAll()
    {
        return Rooms::with(['reservations', 'fixedSchedules'])->get();
    }

    public function find($id)
    {
        return Rooms::with(['reservations.user', 'fixedSchedules'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Rooms::create($data);
    }

    public function update($id, array $data)
    {
        $room = Rooms::findOrFail($id);
        $room->update($data);
        return $room;
    }

    public function delete($id)
    {
        $room = Rooms::findOrFail($id);

        // cek reservasi aktif (pending/approved)
        $activeReservation = $room->reservations()
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        // cek fixed schedule aktif
        $hasFixedSchedule = $room->fixedSchedules()->exists();

        if ($activeReservation || $hasFixedSchedule) {
            throw ValidationException::withMessages([
                'room' => 'Ruangan tidak bisa dihapus karena masih memiliki reservasi aktif atau jadwal tetap.'
            ]);
        }

        return $room->delete();
    }
}
