<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reservations;
use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $reservations = $user->role === 'admin'
            ? Reservations::with('room', 'user')->get()
            : $user->reservations()->with('room')->get();

        return response()->json($reservations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'reason' => 'nullable|string',
        ]);

        $reservation = Reservations::create([
            'user_id' => Auth::id(),
            'room_id' => $request->room_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'reason' => $request->notes,
            'status' => 'pending',
        ]);

        return response()->json($reservation, 201);
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
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'reason' => 'nullable|string',
        ]);

        $reservation->update($request->only(['start_time', 'end_time', 'reason']));
        return response()->json($reservation);
    }

    public function destroy(Reservations $reservation)
    {
        $this->authorizeUser($reservation);
        $reservation->update(['status' => 'cancelled']);
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
