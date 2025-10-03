<?php

namespace App\Services\Karyawan;

use App\Http\Requests\Karyawan\ReservationStoreRequest;
use App\Models\FixedSchedule;
use App\Models\Reservations;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ReservationService
{
    public function create(ReservationStoreRequest $data, $request)
    {


        $data['status'] = 'pending';

        // Parse tanggal & waktu
        $tanggal = Carbon::parse($data['tanggal'])->format('Y-m-d');
        $mulai   = Carbon::parse($tanggal . ' ' . $data['waktu_mulai']);
        $selesai = Carbon::parse($tanggal . ' ' . $data['waktu_selesai']);

        // Validasi waktu
        if ($mulai >= $selesai) {
            throw ValidationException::withMessages([
                'waktu' => 'Waktu mulai harus lebih awal dari waktu selesai.'
            ]);
        }

        // Simpan tanggal & waktu
        $data['tanggal']       = $tanggal;
        $data['hari']          = Carbon::parse($tanggal)->locale('id')->dayName; // auto "Senin"
        $data['waktu_mulai']   = $mulai->format('H:i');
        $data['waktu_selesai'] = $selesai->format('H:i');

        // ✅ Validasi bentrok dengan FixedSchedule (HARUS ditolak)
        $conflictFixed = FixedSchedule::where('room_id', $data['room_id'])
            ->where('day_of_week', strtolower($data['hari']))
            ->where(function ($q) use ($mulai, $selesai) {
                $q->whereBetween('start_time', [$mulai, $selesai])
                  ->orWhereBetween('end_time', [$mulai, $selesai])
                  ->orWhere(function ($q2) use ($mulai, $selesai) {
                      $q2->where('start_time', '<=', $mulai)
                         ->where('end_time', '>=', $selesai);
                  });
            })
            ->exists();

        if ($conflictFixed) {
            throw ValidationException::withMessages([
                'reservation' => 'Bentrok dengan jadwal tetap.'
            ]);
        }

        // // ✅ Cek bentrok dengan reservasi lain, langsung tolak otomatis
        // $conflictReservation = Reservations::overlapping(
        //     $data['room_id'],
        //     $mulai,
        //     $selesai
        // )->whereDate('date', $tanggal)
        //  ->whereIn('status', ['pending', 'approved'])
        //  ->exists();

        // if ($conflictReservation) {
        //     $data['status'] = 'rejected';
        //     $data['reason'] = 'Automatically rejected because the room is already reserved at this time.';
        // }

         // ✅ Cek bentrok dengan reservasi APPROVED → auto reject
        $conflict = Reservations::where('room_id', $data['room_id'])
        ->where('date', $data['date'])
        ->where('status', 'approved')
        ->where(function ($q) use ($mulai, $selesai) {
            $q->whereBetween('start_time', [$mulai, $selesai])
              ->orWhereBetween('end_time', [$mulai, $selesai])
              ->orWhere(function ($q2) use ($mulai, $selesai) {
                  $q2->where('start_time', '<=', $mulai)
                     ->where('end_time', '>=', $selesai);
              });
        })
        ->exists();

        if ($conflict){
            return response()->json([
                'message' => 'Reservation time conflicts with an existing approved reservation.'
            ], 409);
        }

        // Simpan reservasi
        return Reservations::create($data);
    }

    public function getUserReservations($userId)
    {
        return Reservations::with('room')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
