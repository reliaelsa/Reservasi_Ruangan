<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Rooms;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Reservations extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'room_id',
        'date',
        'hari',
        'start_time',
        'end_time',
        'status',
        'reason',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function room()
    {
        return $this->belongsTo(Rooms::class, 'room_id');
    }

    public function scopeOverlapping($query, $roomId, $mulai, $selesai)
    {
        return $query->where('room_id', $roomId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($mulai, $selesai) {
                $q->whereBetween('start_time', [$mulai, $selesai])
                  ->orWhereBetween('end_time', [$mulai, $selesai])
                  ->orWhere(function ($q2) use ($mulai, $selesai) {
                      $q2->where('start_time', '<=', $mulai)
                         ->where('end_time', '>=', $selesai);
                  });
            });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('reservation')
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
                return "Reservation has been {$eventName}";
            });
    }
}
