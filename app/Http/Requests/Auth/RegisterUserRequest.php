<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:' . User::class,
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => 'required|string|in:spectator,creator',
            'accept_terms' => 'required|accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'user_type.required' => 'Please select an account type.',
            'user_type.in' => 'Invalid account type.',
            'accept_terms.required' => 'You must accept the Terms & Conditions.',
            'accept_terms.accepted' => 'You must accept the Terms & Conditions.',
        ];
    }
}
