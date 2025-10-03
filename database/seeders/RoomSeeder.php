<?php

namespace Database\Seeders;

use App\Models\Rooms;
use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
       $rooms = [
    [
        'nama_ruangan' => 'Tokyo Innovation Room',
        'kapasitas'    => 20,
        'deskripsi'    => 'Modern meeting room for creative ideas',
    ],
    [
        'nama_ruangan' => 'Paris Meeting Room',
        'kapasitas'    => 25,
        'deskripsi'    => 'Elegant meeting space with presentation facilities',
    ],
    [
        'nama_ruangan' => 'New York Hall',
        'kapasitas'    => 100,
        'deskripsi'    => 'Large hall for seminars and international events',
    ],
    [
        'nama_ruangan' => 'London Training Room',
        'kapasitas'    => 40,
        'deskripsi'    => 'Classic yet professional training room',
    ],
    [
        'nama_ruangan' => 'Seoul Discussion Room',
        'kapasitas'    => 10,
        'deskripsi'    => 'Small room designed for focused team discussions',
    ],
    [
        'nama_ruangan' => 'Dubai Boardroom',
        'kapasitas'    => 12,
        'deskripsi'    => 'Exclusive boardroom with a luxurious atmosphere',
    ],
    [
        'nama_ruangan' => 'Eiffel Presentation Room',
        'kapasitas'    => 50,
        'deskripsi'    => 'Spacious presentation room with an iconic touch',
    ],
    [
        'nama_ruangan' => 'Amazon Creative Space',
        'kapasitas'    => 15,
        'deskripsi'    => 'Relaxed creative space inspired by nature',
    ],
    [
        'nama_ruangan' => 'Silicon Valley IT Room',
        'kapasitas'    => 8,
        'deskripsi'    => 'Tech-focused room with a startup vibe',
    ],
    [
        'nama_ruangan' => 'Berlin Management Room',
        'kapasitas'    => 30,
        'deskripsi'    => 'Strategic meeting room for management discussions',
    ],
];

        foreach ($rooms as $room) {
            Rooms::create([
                'nama_ruangan' => $room['nama_ruangan'],
                'kapasitas'    => $room['kapasitas'],
                'deskripsi'    => $room['deskripsi'],
                'status'       => 'non-aktif', // default
            ]);
        }
    }
}
