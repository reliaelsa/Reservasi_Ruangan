<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\AuthCode;
use Laravel\Passport\Client;
use Laravel\Passport\DeviceCode;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Passport setup
        Passport::useTokenModel(Token::class);
        Passport::useRefreshTokenModel(RefreshToken::class);
        Passport::useAuthCodeModel(AuthCode::class);
        Passport::useClientModel(Client::class);
        Passport::useDeviceCodeModel(DeviceCode::class);

        // Scramble setup
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });

        /**
         * Custom Validation Rules
         */

        // 1️⃣ Validasi format waktu (H:i)
        Validator::extend('time_format', function ($attribute, $value, $parameters) {
            $format = $parameters[0] ?? 'H:i';
            $d = \DateTime::createFromFormat($format, $value);
            return $d && $d->format($format) === $value;
        });

        // 2️⃣ Validasi durasi maksimum 3 jam
        Validator::extend('max_duration', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();

            $start = isset($data['start_time'])
                ? \DateTime::createFromFormat('H:i', $data['start_time'])
                : null;
            $end = isset($data['end_time'])
                ? \DateTime::createFromFormat('H:i', $data['end_time'])
                : null;

            if (!$start || !$end) {
                return true; // biarkan validasi lain yang menangani
            }

            $diff = ($end->getTimestamp() - $start->getTimestamp()) / 3600;

            return $diff <= 3; // maksimal 3 jam
        });

        // 3️⃣ Validasi booking maksimal H-30 dari tanggal meeting
        Validator::extend('max_booking_days', function ($attribute, $value) {
            $meetingDate = \Carbon\Carbon::parse($value);
            $now = \Carbon\Carbon::now();

            // Harus minimal hari ini dan maksimal 30 hari ke depan
            return $meetingDate->isFuture() && $meetingDate->diffInDays($now) <= 30;
        });
    }
}
