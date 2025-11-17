<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Guard dan password reset bawaan aplikasi.
    |
    */
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Di sini kamu mendefinisikan setiap guard authentication.
    | Kamu pakai Laravel Passport untuk API.
    |
    */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'passport', // pakai Passport untuk API authentication
            'provider' => 'users',
        ],

        // 🟢 Tambahan guard untuk role "karyawan"
        'karyawan' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],

        // (Opsional) kalau kamu juga punya role admin, bisa tambahin juga:
        'admin' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Bagian ini menentukan bagaimana user diambil dari database atau model.
    |
    */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class, // pastikan model kamu adalah User.php
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reset Passwords
    |--------------------------------------------------------------------------
    |
    | Konfigurasi reset password.
    |
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Lama waktu konfirmasi password sebelum kedaluwarsa.
    |
    */
    'password_timeout' => 10800,
];
