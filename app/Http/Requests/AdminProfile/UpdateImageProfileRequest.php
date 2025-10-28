<?php

namespace App\Http\Requests\AdminProfile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

use App\Models\User;

class UpdateImageProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $_user = User::find(Auth::user()->id);
        if ($_user && $_user->hasRole('admin')) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        
        return [
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'image.image' => __('The file must be an image.'),
            'image.mimes' => __('The image must be a file of type: jpeg, png, jpg, gif.'),
            'image.max' => __('The image size must not be greater than 2MB.'),
        ];
    }
}
