<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\ReservationStoreRequest;
use App\Models\Reservations;
use App\Models\FixedSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationCreatedMail;
use App\Mail\ReservationApproveMail;
use App\Mail\ReservationRejectedMail;

class ReservationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $reservations = Reservations::with('room', 'user')->get();
        } else {
            $reservations = $user->reservations()->with('room')->get();
        }

        return response()->json($reservations);
    }

    public function store(ReservationStoreRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';

        // ✅ default reason
        $data['reason'] = $request->input('reason', 'Tidak ada alasan diberikan.');

        // ✅ Parse waktu mulai & selesai
        $mulai   = Carbon::parse($data['date'] . ' ' . $data['start_time']);
        $selesai = Carbon::parse($data['date'] . ' ' . $data['end_time']);

        if ($mulai >= $selesai) {
            throw ValidationException::withMessages([
                'waktu' => 'Waktu mulai harus lebih awal dari waktu selesai.'
            ]);
        }

        $data['start_time'] = $mulai->format('H:i:s');
        $data['end_time']   = $selesai->format('H:i:s');

        // ✅ Ambil nama hari (Monday, Tuesday, dst)
        $dayOfWeek = Carbon::parse($data['date'])->format('l');
        $data['day_of_week'] = $dayOfWeek;

        // ✅ Cek bentrok dengan FixedSchedule (pakai day_of_week)
        $conflictFixed = FixedSchedule::where('room_id', $data['room_id'])
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($q) use ($mulai, $selesai) {
                $q->whereBetween('start_time', [$mulai->format('H:i:s'), $selesai->format('H:i:s')])
                  ->orWhereBetween('end_time', [$mulai->format('H:i:s'), $selesai->format('H:i:s')])
                  ->orWhere(function ($q2) use ($mulai, $selesai) {
                      $q2->where('start_time', '<=', $mulai->format('H:i:s'))
                         ->where('end_time', '>=', $selesai->format('H:i:s'));
                  });
            })
            ->exists();

        if ($conflictFixed) {
            $data['status'] = 'rejected';
            $data['reason'] = 'Ditolak otomatis karena bentrok dengan jadwal tetap.';
        }

        // ✅ Cek bentrok dengan reservasi lain (approved saja)
        $conflictReservation = Reservations::where('room_id', $data['room_id'])
            ->whereDate('date', $data['date'])
            ->where(function ($q) use ($mulai, $selesai) {
                $q->whereBetween('start_time', [$mulai->format('H:i:s'), $selesai->format('H:i:s')])
                  ->orWhereBetween('end_time', [$mulai->format('H:i:s'), $selesai->format('H:i:s')])
                  ->orWhere(function ($q2) use ($mulai, $selesai) {
                      $q2->where('start_time', '<=', $mulai->format('H:i:s'))
                         ->where('end_time', '>=', $selesai->format('H:i:s'));
                  });
            })
            ->where('status', 'approved')
            ->exists();

        if ($conflictReservation) {
            $data['status'] = 'rejected';
            $data['reason'] = 'Ditolak otomatis karena sudah ada reservasi lain pada waktu ini.';
        }

        // ✅ Simpan reservasi
        $reservation = Reservations::create($data);

        Mail::to('admin1@example.com')->send(new ReservationCreatedMail($reservation));
        return response()->json([
            'data' => [
                'id'          => $reservation->id,
                'room'        => [
                    'id'   => $reservation->room->id,
                    'name' => $reservation->room->name,
                ],
                'date'        => Carbon::parse($reservation->date)->format('Y-m-d'),
                'day_of_week' => $reservation->day_of_week ?? $dayOfWeek,
                'start_time'  => $reservation->start_time,
                'end_time'    => $reservation->end_time,
                'status'      => $reservation->status,
                'reason'      => $reservation->reason,
                'created_at'  => $reservation->created_at->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }

    public function show(Reservations $reservation)
    {
        $this->authorizeUser($reservation);
        return response()->json($reservation->load('room', 'user'));
    }

    public function update(Request $request, Reservations $reservation)
    {
        $this->authorizeUser($reservation);

        $request->validate([
            'start_time' => 'required|date_format:H:i:s',
            'end_time'   => 'required|date_format:H:i:s|after:start_time',
            'reason'     => 'nullable|string',
        ]);

        $reservation->update($request->only(['start_time', 'end_time', 'reason']));
        return response()->json($reservation);
    }

    public function destroy(Reservations $reservation)
    {
        $this->authorizeUser($reservation);
        $reservation->update(['status' => 'canceled']);
        return response()->json(['message' => 'Reservation cancelled']);
    }

    public function approve(Reservations $reservation)
    {
        $reservation->update(['status' => 'approved']);
        return response()->json($reservation);
    }

    public function reject(Reservations $reservation)
    {
        $reservation->update(['status' => 'rejected']);
        return response()->json($reservation);
    }

    private function authorizeUser(Reservations $reservation)
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $reservation->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
    }
}
