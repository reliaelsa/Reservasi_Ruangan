<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

class ReservationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id'    => 'required|integer|exists:rooms,id',
            'date'       => [
                'required',
                'date',
                'after_or_equal:today',
                function ($attribute, $value, $fail) {
                    // 🔹 Maksimal H-30 sebelum tanggal meeting
                    $diff = Carbon::now()->diffInDays(Carbon::parse($value), false);
                    if ($diff > 30) {
                        $fail('Reservasi hanya bisa dilakukan maksimal 30 hari sebelum tanggal meeting.');
                    }
                }
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time'   => [
                'required',
                'date_format:H:i',
                'after:start_time',
                function ($attribute, $value, $fail) {
                    // 🔹 Maksimal durasi 3 jam per meeting
                    $start = Carbon::parse(request()->start_time);
                    $end = Carbon::parse($value);
                    if ($start->diffInHours($end) > 3) {
                        $fail('Durasi meeting maksimal hanya 3 jam.');
                    }
                }
            ],
            'reason' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'ID ruangan wajib diisi.',
            'room_id.exists' => 'Ruangan tidak ditemukan.',
            'date.required' => 'Tanggal reservasi wajib diisi.',
            'date.after_or_equal' => 'Tanggal reservasi tidak boleh kurang dari hari ini.',
            'start_time.required' => 'Waktu mulai wajib diisi.',
            'start_time.date_format' => 'Format waktu mulai harus HH:mm.',
            'end_time.required' => 'Waktu selesai wajib diisi.',
            'end_time.date_format' => 'Format waktu selesai harus HH:mm.',
            'end_time.after' => 'Waktu selesai harus lebih besar dari waktu mulai.',
            'reason.max' => 'Alasan reservasi maksimal 500 karakter.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
