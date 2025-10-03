<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\rooms;
use Carbon\Carbon;

class Reservations extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'date',        // ✅ sesuai migration
        'hari',        // ✅ info tambahan, nullable
        'start_time',
        'end_time',
        'status',
        'reason',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    // protected static function booted()
    // {
    //     static::creating(function ($reservation) {
    //         if (!empty($reservation->date) && !empty($reservation->start_time)) {
    //             $datetime = Carbon::parse($reservation->date . ' ' . $reservation->start_time);
    //             $reservation->day = $datetime->translatedFormat('l');
    //             // ->locale('id')
    //         }
    //     });
    // }

    // public function getStartTimeAttribute()
    // {
    //     return $this->attributes['start_time'] ?? null;
    // }

    protected $casts = [
        'date'       => 'date', // ✅ cast jadi tanggal saja
        // 'waktu_mulai'   => 'string',     // ✅ simpan sebagai string (format H:i)
        // 'waktu_selesai' => 'string',     // ✅ simpan sebagai string (format H:i)
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function room()
    {
        return $this->belongsTo(Rooms::class,'room_id');
    }

    /**
     * Scope untuk mencari overlapping reservation
     */
    public function scopeOverlapping($query, $roomId, $mulai, $selesai)
    {
        return $query->where('room_id', $roomId)
            ->whereIn('status', ['pending','approved'])
            ->where(function ($q) use ($mulai, $selesai) {
                $q->whereBetween('start_time', [$mulai, $selesai])
                  ->orWhereBetween('end_time', [$mulai, $selesai])
                  ->orWhere(function ($q2) use ($mulai, $selesai) {
                      $q2->where('start_time', '<=', $mulai)
                         ->where('end_time', '>=', $selesai);
                  });
            });
    }
}
