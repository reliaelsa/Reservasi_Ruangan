<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class rooms extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity',
        'description',
        'status', // default di DB = "non-aktif"
    ];
        protected $attributes = [
        'status' => 'inactive',
    ];
    // Relasi ke Reservations
    public function reservations()
    {
        return $this->hasMany(Reservations::class,'room_id');
    }

    // Relasi ke FixedSchedules
    public function fixedSchedules()
    {
        return $this->hasMany(FixedSchedule::class);
    }

    // Semua user yang pernah booking ruangan ini
    public function users()
    {
        return $this->belongsToMany(User::class, 'reservations')
                    ->withPivot(['tanggal', 'waktu_mulai', 'waktu_selesai', 'status'])
                    ->withTimestamps();
    }

    /**
     * Accessor status_aktual (real-time).
     * Akan bernilai "aktif" jika ada reservasi approved
     * di tanggal & jam sekarang.
     */
    public function getStatusAktualAttribute()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $timeNow = $now->format('H:i:s');

        $adaReservasi = $this->reservations()
            ->where('tanggal', $today)
            ->where('status', 'approved')
            ->where('waktu_mulai', '<=', $timeNow)
            ->where('waktu_selesai', '>=', $timeNow)
            ->exists();

        return $adaReservasi ? 'aktif' : 'non-aktif';
    }
}
