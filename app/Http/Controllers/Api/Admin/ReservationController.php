<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reservations;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationApproveMail;
use App\Mail\ReservationRejectedMail;
use Carbon\Carbon;

class ReservationController extends Controller
{
    public function approve(Request $request, $id)
    {
        $reservation = Reservations::findOrFail($id);

        $reason = $request->input('reason', 'Disetujui');
        $reservation->status = 'approved';
        $reservation->reason = $reason;

        // 🔥 isi otomatis hari sesuai tanggal
        $hari = Carbon::parse($reservation->date)->translatedFormat('l'); // contoh: Senin, Selasa, dst
        $reservation->hari = $hari;

        $reservation->save();

        // 🔥 Cancel otomatis semua reservasi lain yang bentrok
        Reservations::where('room_id', $reservation->room_id)
            ->where('date', $reservation->date)
            ->where('id', '!=', $reservation->id)
            ->where(function ($query) use ($reservation) {
                $query->whereBetween('start_time', [$reservation->start_time, $reservation->end_time])
                      ->orWhereBetween('end_time', [$reservation->start_time, $reservation->end_time])
                      ->orWhere(function ($q) use ($reservation) {
                          $q->where('start_time', '<=', $reservation->start_time)
                            ->where('end_time', '>=', $reservation->end_time);
                      });
            })
            ->update([
                'status' => 'rejected',
                'reason' => 'Reservasi dibatalkan otomatis karena bentrok dengan reservasi lain yang sudah disetujui.'
            ]);

        // Kirim Email ke user (optional)
        Mail::to($reservation->user->email)->send(new ReservationApproveMail($reservation, $reason));

        return response()->json([
            'message' => 'Reservasi berhasil disetujui, reservasi bentrok otomatis dibatalkan.',
            'data'    => $reservation,
            'reason'  => $reason
        ]);
    }

    public function reject(Request $request, $id)
    {
        $reservation = Reservations::findOrFail($id);

        $reason = $request->input('reason', 'Tidak ada alasan diberikan.');
        $reservation->status = 'rejected';
        $reservation->reason = $reason;

        // 🔥 isi otomatis hari sesuai tanggal
        $hari = Carbon::parse($reservation->date)->translatedFormat('l');
        $reservation->hari = $hari;

        $reservation->save();

        // Kirim Email ke user (optional)
        Mail::to($reservation->user->email)->send(new ReservationRejectedMail($reservation, $reason));

        return response()->json([
            'message' => 'Reservasi berhasil ditolak & notifikasi email terkirim.',
            'data'    => $reservation,
            'reason'  => $reason
        ]);
    }
}
