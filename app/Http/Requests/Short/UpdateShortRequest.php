<?php

namespace App\Http\Requests\Short;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateShortRequest extends FormRequest
{
    /**
     * Determine if the category is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();   
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'text_caption' => 'nullable|string',
        ];
    }
}
