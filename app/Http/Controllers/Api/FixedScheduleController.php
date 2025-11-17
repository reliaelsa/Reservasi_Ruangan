<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FixedSchedule; // Pastikan model ini sudah di-import

class FixedScheduleController extends Controller
{
    /**
     * fixed-schedule.index

     */
    public function index(Request $request)
    {
        // 1. Validasi request untuk semua parameter filter dan pagination
        $request->validate([
            'day_of_week' => 'nullable|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'room_id' => 'nullable|integer|exists:rooms,id',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'search' => 'nullable|string', // Untuk pencarian deskripsi atau nama ruangan
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // ambil jumlah data per halaman (default 10)
        $perPage = $request->input('per_page', 10);

        // ambil halaman ke berapa (default 1)
        $page = $request->input('page', 1);

        // query dasar
        $query = FixedSchedule::with(['room', 'user']);

        // 2. Filtering berdasarkan parameter yang diminta (langsung dari request)

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->input('day_of_week'));
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
        }

        // Filter Waktu Mulai (start_time)
        if ($request->filled('start_time')) {
            $query->where('start_time', '>=', $request->input('start_time'));
        }

        // Filter Waktu Selesai (end_time)
        if ($request->filled('end_time')) {
            $query->where('end_time', '<=', $request->input('end_time'));
        }

        // Filter Pencarian (Search: deskripsi atau nama ruangan)
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($searchTerm) {
                // Mencari di kolom description
                $q->where('description', 'like', $searchTerm)
                  // atau mencari di kolom nama ruangan melalui relasi
                  ->orWhereHas('room', function ($qRoom) use ($searchTerm) {
                      $qRoom->where('name', 'like', $searchTerm);
                  });
            });
        }

        // pagination
        $data = $query->paginate($perPage, ['*'], 'page', $page);

        // kalau datanya kosong
        if ($data->isEmpty() && $page > 1) {
             return response()->json([
                'status' => 'error',
                'message' => 'Halaman tidak ditemukan.'
            ], 404);
        } elseif ($data->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data jadwal tetap tidak ditemukan.',
            ], 200);
        }

        // kalau datanya ada
        return response()->json([
            'status' => 'success',
            'message' => 'Daftar jadwal tetap berhasil diambil.',
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ],
        ], 200);
    }

    /**
     * fixed-schedule.create
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id'     => 'required|exists:rooms,id',
            'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string',
        ]);

        $schedule = FixedSchedule::create($request->all());

        return response()->json($schedule, 201);
    }

    /**
     * fixed-schedule.detail

     */
    public function show(FixedSchedule $fixedSchedule)
    {
        return response()->json($fixedSchedule->load(['room', 'user']));
    }

    /**
     * fixed-schedule.update

     */
    public function update(Request $request, FixedSchedule $fixedSchedule)
    {
        $request->validate([
            'room_id'     => 'required|exists:rooms,id',
            'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string',
        ]);

        $fixedSchedule->update($request->all());

        return response()->json($fixedSchedule);
    }

    /**
     * fixed-schedule.delete

     */
    public function destroy(FixedSchedule $fixedSchedule)
    {
        $fixedSchedule->delete();
        return response()->json(['message' => 'Fixed schedule deleted']);
    }
}
