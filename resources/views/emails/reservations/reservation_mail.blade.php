<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reservasi Baru</title>
</head>
<body>
    <h2>Ada Reservasi Baru</h2>
    <p><strong>Ruangan:</strong> {{ $reservation->room->name }}</p>
    <p><strong>Tanggal:</strong> {{ $reservation->date }}</p>
    <p><strong>Waktu:</strong> {{ $reservation->start_time }} - {{ $reservation->end_time }}</p>
    <p><strong>Status:</strong> {{ $reservation->status }}</p>
</body>
</html>
