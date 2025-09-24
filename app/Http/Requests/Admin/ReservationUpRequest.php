<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReservationUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'status' => 'required|in:approved,rejected,canceled',
        'reason' => 'nullable|string|max:255',
    ];
}
}
