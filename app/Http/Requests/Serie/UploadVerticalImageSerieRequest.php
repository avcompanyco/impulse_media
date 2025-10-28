<?php

namespace App\Http\Requests\Serie;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UploadVerticalImageSerieRequest extends FormRequest
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
            'vertical_image' => 'required|file|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:10240', // 10MB max
        ];
    }
}
