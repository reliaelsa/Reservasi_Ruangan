<?php

namespace App\Exports;

use App\Models\Reservations;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReservationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Ambil data reservasi berdasarkan rentang tanggal
     */
    public function collection()
    {
        return Reservations::with(['user', 'room'])
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Tentukan kolom yang akan muncul di file Excel
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nama User',
            'Email User',
            'Ruangan',
            'Tanggal',
            'Hari',
            'Jam Mulai',
            'Jam Selesai',
            'Status',
            'Alasan',
            'Dibuat Pada',
            'Diperbarui Pada'
        ];
    }

    /**
     * Tentukan isi tiap baris
     */
    public function map($reservation): array
    {
        return [
            $reservation->id,
            optional($reservation->user)->name,
            optional($reservation->user)->email,
            optional($reservation->room)->name,
            $reservation->date,
            $reservation->hari,
            date('H:i', strtotime($reservation->start_time)),
            date('H:i', strtotime($reservation->end_time)),
            ucfirst($reservation->status),
            $reservation->reason ?? '-',
            $reservation->created_at->format('Y-m-d H:i:s'),
            $reservation->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
