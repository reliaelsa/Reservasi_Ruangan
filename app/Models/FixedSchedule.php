<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedSchedule extends Model
{
    use HasFactory;

    protected $table = 'fixed_schedule';

    protected $fillable = [
        'user_id',
        'room_id',
        'date',
        'day_of_week',
        'start_time',
        'end_time',
        'description',
        'status',
    ];

    protected $casts = [
        'date'       => 'date:Y-m-d',
        'start_time' => 'string',
        'end_time'   => 'string',
    ];

    public function room()
    {
        return $this->belongsTo(Rooms::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservations::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for overlapping schedules
     */
    public function scopeOverlapping($query, $roomId, $start, $end)
    {
        return $query->where('room_id', $roomId)
            ->whereIn('status', ['pending','approved'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                  ->orWhereBetween('end_time', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_time', '<=', $start)
                         ->where('end_time', '>=', $end);
                  });
            });
    }
}
