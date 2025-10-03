<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FixedSchedule;

class FixedScheduleController extends Controller
{
    public function index()
    {
        return response()->json(FixedSchedule::with('room')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_id'     => 'required|exists:rooms,id',
            'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string',
        ]);

        $schedule = FixedSchedule::create([
            'room_id'     => $request->room_id,
            'day_of_week' => $request->day_of_week,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'description' => $request->description,
        ]);

        return response()->json($schedule, 201);
    }

    public function show(FixedSchedule $fixedSchedule)
    {
        return response()->json($fixedSchedule->load('room'));
    }

    public function update(Request $request, FixedSchedule $fixedSchedule)
    {
        $request->validate([
            'room_id'     => 'required|exists:rooms,id',
            'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string',
        ]);

        $fixedSchedule->update([
            'room_id'     => $request->room_id,
            'day_of_week' => $request->day_of_week,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'description' => $request->description,
        ]);

        return response()->json($fixedSchedule);
    }

    public function destroy(FixedSchedule $fixedSchedule)
    {
        $fixedSchedule->delete();
        return response()->json(['message' => 'fixed schedule deleted']);
    }
}
