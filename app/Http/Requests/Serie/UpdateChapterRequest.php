<?php

namespace App\Http\Requests\Serie;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateChapterRequest extends FormRequest
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
            'chapter_number' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
        ];
    }
}
