<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservations;
use App\Models\Room;
use App\Models\FixedSchedule;
use App\Models\rooms;
use App\Models\User;
use Illuminate\Support\Facades\DB;

 class DashboardController extends Controller
{
    public function stats()
    {
        // Hitung total data
        $roomsCount = Rooms::count();
        $reservationsCount = Reservations::count();
        $fixedSchedulesCount = FixedSchedule::count();
        $usersCount = User::count();

        $monthNow = now()->month;
        $monthPrev = now()->subMonth()->month;
        $yearNow = now()->year;

        // Fungsi bantu untuk hitung data per bulan
        $getCount = function ($model, $month, $column = 'created_at') use ($yearNow) {
            return $model::whereYear($column, $yearNow)
                ->whereMonth($column, $month)
                ->count() ?? 0;
        };

        // Ambil data bulan ini dan sebelumnya
        $reservationNow = $getCount(Reservations::class, $monthNow, 'created_at');
        $reservationPrev = $getCount(Reservations::class, $monthPrev, 'created_at');
        $roomNow = $getCount(rooms::class, $monthNow);
        $roomPrev = $getCount(Rooms::class, $monthPrev);
        $scheduleNow = $getCount(FixedSchedule::class, $monthNow);
        $schedulePrev = $getCount(FixedSchedule::class, $monthPrev);
        $userNow = $getCount(User::class, $monthNow);
        $userPrev = $getCount(User::class, $monthPrev);

        // Fungsi bantu hitung persen perubahan
        $percentChange = function ($now, $prev) {
            if ($prev > 0) {
                return round((($now - $prev) / $prev) * 100, 1);
            } elseif ($now > 0 && $prev == 0) {
                return 100;
            }
            return 0;
        };

        // Hitung perubahan persentase
        $reservationChange = $percentChange($reservationNow, $reservationPrev);
        $roomChange = $percentChange($roomNow, $roomPrev);
        $scheduleChange = $percentChange($scheduleNow, $schedulePrev);
        $userChange = $percentChange($userNow, $userPrev);

        // Ambil data reservasi per bulan untuk chart
        $monthlyReservations = Reservations::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', $yearNow)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Siapkan data untuk 12 bulan
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $data = array_fill(0, 12, 0);

        foreach ($monthlyReservations as $row) {
            $index = $row->month - 1;
            $data[$index] = $row->total;
        }

        return response()->json([
            'total' => [
                'reservations' => $reservationsCount,
                'rooms' => $roomsCount,
                'fixedSchedules' => $fixedSchedulesCount,
                'users' => $usersCount,
            ],
            'change' => [
                'reservations' => $reservationChange,
                'rooms' => $roomChange,
                'fixedSchedules' => $scheduleChange,
                'users' => $userChange,
            ],
            'currentMonth' => $labels[$monthNow - 1],
            'chart' => [
                'labels' => $labels,
                'data' => $data,
                'year' => $yearNow,
            ],
        ]);
    }
}
