<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use Illuminate\Http\Request;
use App\Models\Rooms;

class RoomController extends Controller
{
    /**
     * room.index
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = $user->getRoleNames()->first();

        $query = Rooms::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('capacity')) {
            $query->where('capacity', '>=', $request->input('capacity'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 🟢 Kalau admin, ambil juga relasi reservations + user + room
        if ($role === 'admin') {
            $query->with(['reservations.user', 'reservations.room']);
        }

        $rooms = $query->get(); // ambil semua data sekaligus, tidak paginate

        // 🟢 Format response sesuai role
        if ($role === 'admin') {
            $data = $rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'capacity' => $room->capacity,
                    'description' => $room->description,
                    'status' => $room->status,
                    'reservations' => $room->reservations->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'user' => [
                                'id' => $r->user->id ?? null,
                                'name' => $r->user->name ?? null,
                                'email' => $r->user->email ?? null,
                            ],
                            'room' => [
                                'id' => $r->room->id ?? null,
                                'name' => $r->room->name ?? null,
                            ],
                            'date' => $r->date,
                            'day_of_week' => $r->day_of_week,
                            'start_time' => $r->start_time,
                            'end_time' => $r->end_time,
                            'status' => $r->status,
                            'reason' => $r->reason,
                            'created_at' => $r->created_at,
                            'updated_at' => $r->updated_at,
                        ];
                    }),
                ];
            });
        } else {
            // 🟡 Karyawan lihat data ruangan + deskripsi saja
            $data = $rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'capacity' => $room->capacity,
                    'description' => $room->description,
                    'status' => $room->status,
                ];
            });
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data ruangan berhasil diambil',
            'data' => $data,
            'role' => $role,
        ], 200);
    }

    /**
     * room.store
     */
    public function store(RoomRequest $request)
    {
        $room = Rooms::create($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Ruangan berhasil dibuat',
            'data' => $room,
        ], 201);
    }

    /**
     * room.show
     */
    public function show(Rooms $room)
    {
        $user = auth()->user();
        $role = $user->getRoleNames()->first();

        if ($role === 'admin') {
            $room->load(['reservations.user', 'reservations.room']);
            $data = [
                'id' => $room->id,
                'name' => $room->name,
                'capacity' => $room->capacity,
                'description' => $room->description,
                'status' => $room->status,
                'reservations' => $room->reservations->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'user' => [
                            'id' => $r->user->id ?? null,
                            'name' => $r->user->name ?? null,
                            'email' => $r->user->email ?? null,
                        ],
                        'room' => [
                            'id' => $r->room->id ?? null,
                            'name' => $r->room->name ?? null,
                        ],
                        'date' => $r->date,
                        'day_of_week' => $r->day_of_week,
                        'start_time' => $r->start_time,
                        'end_time' => $r->end_time,
                        'status' => $r->status,
                        'reason' => $r->reason,
                        'created_at' => $r->created_at,
                        'updated_at' => $r->updated_at,
                    ];
                }),
            ];
        } else {
            $data = [
                'id' => $room->id,
                'name' => $room->name,
                'capacity' => $room->capacity,
                'description' => $room->description,
                'status' => $room->status,
            ];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail ruangan berhasil diambil',
            'data' => $data,
            'role' => $role,
        ], 200);
    }

    /**
     * room.update
     */
    public function update(RoomRequest $request, Rooms $room)
    {
        $room->update($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Ruangan berhasil diperbarui',
            'data' => $room,
        ], 200);
    }

    /**
     * room.delete
     */
    public function destroy(Rooms $room)
    {
        if ($room->reservations()->whereIn('status', ['pending', 'approved'])->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ruangan tidak bisa dihapus karena masih memiliki reservasi aktif',
            ], 400);
        }

        $room->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Ruangan berhasil dihapus',
        ], 200);
    }
}
