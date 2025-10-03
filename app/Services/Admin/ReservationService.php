<?php

namespace App\Services\Admin;

use App\Models\Reservations;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\ReservationApproveMail;
use App\Mail\ReservationRejectedMail;
use App\Mail\ReservationCancelByOverlapMail;
use App\Http\Resources\Karyawan\ReservationResource;

class ReservationService
{
    public function getAll()
    {
        return Reservations::with(['user', 'room'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_mulai')
            ->get();
    }

    /**
     * Buat reservasi baru
     * Kalau ada yang sudah approved dan bentrok → auto rejected
     */
    public function createReservation(array $data)
    {
        return DB::transaction(function () use ($data) {
            // cari reservasi bentrok yang sudah approved
            $conflict = Reservations::where('room_id', $data['room_id'])
                ->where('tanggal', $data['tanggal'])
                ->where('status', 'approved')
                ->where(function ($q) use ($data) {
                    $q->where('waktu_mulai', '<', $data['waktu_selesai'])
                      ->where('waktu_selesai', '>', $data['waktu_mulai']);
                })
                ->exists();

            if ($conflict) {
                // langsung tolak karena bentrok
                $data['status'] = 'rejected';
                $data['reason'] = 'Ditolak otomatis karena user sudah punya reservasi pada waktu ini.';
            } else {
                // default pending
                $data['status'] = 'pending';
            }

            $reservation = Reservations::create($data);

            return response()->json([
                'data' => new ReservationResource($reservation)
            ]);
        });
    }

    public function approve($id)
    {
        return $this->updateStatus($id, 'approved');
    }

    public function reject($id, $reason = null)
    {
        return $this->updateStatus($id, 'rejected', $reason);
    }

    public function delete($id)
    {
        $reservation = Reservations::findOrFail($id);
        $reservation->delete();
        return true;
    }

    /**
     * Update status reservasi
     * Kalau approve → auto reject semua yang bentrok
     */
    public function updateStatus($id, string $status, $reason = null)
    {
        $reservation = Reservations::with(['user', 'room'])->findOrFail($id);

        if ($status === 'approved') {
            DB::transaction(function () use ($reservation) {
                $reservation->update(['status' => 'approved']);

                if ($reservation->user) {
                    Mail::to($reservation->user->email)
                        ->queue(new ReservationApproveMail($reservation));
                }

                // cari pending lain yang bentrok
                $overlaps = Reservations::where('room_id', $reservation->room_id)
                    ->where('hari', $reservation->hari)
                    ->where('id', '!=', $reservation->id)
                    ->whereIn('status', ['pending'])
                    ->where(function ($q) use ($reservation) {
                        $q->where('waktu_mulai', '<', $reservation->waktu_selesai)
                          ->where('waktu_selesai', '>', $reservation->waktu_mulai);
                    })
                    ->get();

                foreach ($overlaps as $overlap) {
                    $overlap->update([
                        'status' => 'rejected',
                        'reason' => 'Ditolak otomatis karena user sudah punya reservasi pada waktu ini.'
                    ]);

                    if ($overlap->user) {
                        Mail::to($overlap->user->email)
                            ->queue(new ReservationCancelByOverlapMail($overlap, $reservation));
                    }
                }
            });
        }

        if ($status === 'rejected') {
            $reservation->update([
                'status' => 'rejected',
                'reason' => $reason ?? 'Ditolak oleh admin'
            ]);

            if ($reservation->user) {
                Mail::to($reservation->user->email)
                    ->queue(new ReservationRejectedMail($reservation, $reason));
            }
        }

        return response()->json([
            'data' => new ReservationResource($reservation)
        ]);
    }
}
