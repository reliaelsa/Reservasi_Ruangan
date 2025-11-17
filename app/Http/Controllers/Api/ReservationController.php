<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservations;
use Illuminate\Http\Request;
use App\Models\FixedSchedule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReservationsExport;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    // =========================================================================
    // 📊 STATISTIK RESERVASI BULANAN (Dashboard)
    // =========================================================================
    public function monthlyStatistics()
    {
        $currentYear = date('Y');

        $rawMonthlyData = DB::table('reservations')
            ->select(
                DB::raw('MONTH(date) as bulan'),
                DB::raw('COUNT(id) as jumlah')
            )
            ->whereYear('date', $currentYear)
            ->whereIn('status', ['approved', 'pending'])
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $monthlyCounts = array_fill(1, 12, 0);

        foreach ($rawMonthlyData as $data) {
            $monthlyCounts[$data->bulan] = (int) $data->jumlah;
        }

        return response()->json(array_values($monthlyCounts));
    }

    // =========================================================================
    // 📦 EXPORT RESERVASI KE EXCEL
    // =========================================================================
    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate || !$endDate) {
            return response()->json(['message' => 'Tanggal mulai dan tanggal akhir wajib diisi.'], 400);
        }

        $fileName = 'reservations_' . $startDate . '_to_' . $endDate . '.xlsx';
        return Excel::download(new ReservationsExport($startDate, $endDate), $fileName);
    }

    // =========================================================================
    // 📋 GET /api/reservations
    // =========================================================================
    public function index(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
            'day_of_week' => 'nullable|string',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'status' => 'nullable|in:pending,approved,rejected,cancelled',
        ]);

        $query = Reservations::with(['user', 'room']);

        if ($request->filled('date')) $query->whereDate('date', $request->date);
        if ($request->filled('day_of_week')) $query->where('hari', $request->day_of_week);
        if ($request->filled('start_time')) $query->where('start_time', '>=', $request->start_time);
        if ($request->filled('end_time')) $query->where('end_time', '<=', $request->end_time);
        if ($request->filled('status')) $query->where('status', $request->status);

        if (Auth::user()->role === 'user') $query->where('user_id', Auth::id());

        $reservations = $query->orderBy('date', 'desc')->orderBy('start_time', 'desc')->get();

        $data = $reservations->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'user' => [
                    'id' => $reservation->user->id,
                    'name' => $reservation->user->name,
                    'email' => $reservation->user->email,
                ],
                'room' => [
                    'id' => $reservation->room->id,
                    'nama_ruangan' => $reservation->room->name,
                ],
                'tanggal' => $reservation->date,
                'day_of_week' => $reservation->hari,
                'start_time' => date('H:i', strtotime($reservation->start_time)),
                'end_time' => date('H:i', strtotime($reservation->end_time)),
                'status' => $reservation->status,
                'reason' => $reservation->reason,
                'created_at' => $reservation->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $reservation->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Reservation list retrieved successfully.',
            'data' => $data
        ]);
    }

    // =========================================================================
    // 📝 POST /api/reservations
    // =========================================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string'
        ]);

        $today = now();
        $reservationDate = Carbon::parse($request->date);
        $maxBookingDate = $today->copy()->addDays(30);

        if ($reservationDate->lt($today->startOfDay())) {
            return response()->json(['status' => 'error', 'message' => 'Tanggal reservasi tidak boleh di masa lalu.'], 422);
        }

        if ($reservationDate->gt($maxBookingDate)) {
            return response()->json(['status' => 'error', 'message' => 'Reservasi hanya bisa dilakukan maksimal 30 hari sebelum tanggal meeting.'], 422);
        }

        $start = Carbon::createFromFormat('H:i', $request->start_time);
        $end = Carbon::createFromFormat('H:i', $request->end_time);
        if ($start->diffInHours($end) > 3) {
            return response()->json(['status' => 'error', 'message' => 'Durasi meeting maksimal adalah 3 jam.'], 422);
        }

        $fixed = FixedSchedule::where('room_id', $request->room_id)
            ->where('day_of_week', $reservationDate->dayOfWeek)
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_time', [$request->start_time, $request->end_time])
                  ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })->exists();

        if ($fixed) {
            return response()->json(['status' => 'error', 'message' => 'Jadwal bentrok dengan fixed schedule.'], 400);
        }

        $overlap = Reservations::where('room_id', $request->room_id)
            ->whereDate('date', $request->date)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_time', [$request->start_time, $request->end_time])
                  ->orWhereBetween('end_time', [$request->start_time, $request->end_time]);
            })->exists();

        if ($overlap) {
            return response()->json(['status' => 'error', 'message' => 'Waktu yang dipilih sudah dipesan.'], 400);
        }

        $data = [
            'room_id' => $request->room_id,
            'user_id' => Auth::id(),
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->reason,
            'status' => 'pending',
            'hari' => $reservationDate->translatedFormat('l'),
        ];

        $reservation = Reservations::create($data);

        return response()->json(['status' => 'success', 'message' => 'Reservation created successfully.', 'data' => $reservation], 201);
    }

    // =========================================================================
    // 🔍 SHOW /api/reservations/{id}
    // =========================================================================
    public function show($id)
    {
        $reservation = Reservations::with(['user', 'room'])->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $reservation]);
    }

    // =========================================================================
    // ❌ CANCEL (User)
    // =========================================================================
    public function cancel(Request $request, $id)
    {
        $reservation = Reservations::findOrFail($id);
        if ($reservation->user_id != Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        if ($reservation->status === 'approved') {
            return response()->json(['status' => 'error', 'message' => 'Reservasi yang sudah disetujui tidak dapat dibatalkan oleh user.'], 403);
        }

        $reservation->update([
            'status' => 'cancelled',
            'reason' => $request->reason ?? $reservation->reason
        ]);

        return response()->json(['status' => 'success', 'message' => 'Reservasi dibatalkan.', 'data' => $reservation]);
    }

    // =========================================================================
    // ✅ APPROVE (Admin)
    // =========================================================================
    public function approve($id)
    {
        $reservation = Reservations::with(['user', 'room'])->findOrFail($id);

        Reservations::where('room_id', $reservation->room_id)
            ->whereDate('date', $reservation->date)
            ->where('id', '!=', $id)
            ->whereIn('status', ['pending', 'approved'])
            ->update(['status' => 'cancelled']);

        $reservation->update(['status' => 'approved']);

        return response()->json(['status' => 'success', 'message' => 'Reservasi disetujui.', 'data' => $reservation]);
    }

    // =========================================================================
    // 🚫 REJECT (Admin)
    // =========================================================================
    public function reject($id)
    {
        $reservation = Reservations::findOrFail($id);
        $reservation->update(['status' => 'rejected']);

        return response()->json(['status' => 'success', 'message' => 'Reservation rejected successfully.']);
    }

    // =========================================================================
    // 🔁 UPDATE STATUS (Admin - fleksibel)
    // =========================================================================
    public function updateStatus(Request $request, $id, $action)
    {
        $reservation = Reservations::find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Reservasi tidak ditemukan'], 404);
        }

        $allowedActions = ['approved', 'rejected', 'pending', 'canceled'];
        if (!in_array($action, $allowedActions)) {
            return response()->json(['message' => 'Status tidak valid'], 400);
        }

        $reservation->status = $action;

        // ✅ PERBAIKAN: Ubah dari 'alasan' menjadi 'reason'
        if ($action === 'rejected') {
            $reservation->reason = $request->alasan ?? '-';
        }

        $reservation->save();

        return response()->json([
            'message' => "Status reservasi berhasil diubah menjadi {$action}",
            'data' => $reservation
        ]);
    }

    // =========================================================================
    // 🗑️ DELETE RESERVATION
    // =========================================================================
    public function destroy($id)
    {
        $reservation = Reservations::findOrFail($id);
        $reservation->delete();

        return response()->json(['status' => 'success', 'message' => 'Reservation deleted successfully.']);
    }
}
