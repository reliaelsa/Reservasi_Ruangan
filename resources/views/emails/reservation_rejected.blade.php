<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Ditolak</title>
</head>
<body>
    <h2>Halo, {{ $reservation->user->name }} 👋</h2>

    <p>Mohon maaf, reservasi Anda <strong>DITOLAK</strong> ❌</p>

    <ul>
        <li><strong>Ruangan:</strong> {{ $reservation->room->name ?? '-' }}</li>
        <li><strong>Hari:</strong> {{ $reservation->hari ?? '-' }}</li>
        <li><strong>Tanggal:</strong>
            {{ $reservation->date ? \Carbon\Carbon::parse($reservation->date)->format('d M Y') : '-' }}
            ({{ $reservation->hari ?? '-' }})
        </li>
        <li><strong>Waktu:</strong>
            {{ $reservation->start_time ? substr($reservation->start_time,0,5) : '-' }}
            -
            {{ $reservation->end_time ? substr($reservation->end_time,0,5) : '-' }}
        </li>
    </ul>

    <p><strong>Alasan Penolakan:</strong> {{ $reason ?? 'Tidak ada alasan diberikan.' }}</p>

    <p>Silakan ajukan ulang reservasi dengan jadwal yang berbeda.</p>
</body>
</html>
