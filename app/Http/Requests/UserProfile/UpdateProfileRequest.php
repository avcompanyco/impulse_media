<?php

namespace App\Http\Requests\UserProfile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Models\User;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $_user = User::find(Auth::user()->id);
        if ($_user && $_user->hasAnyRole(['user', 'spectator', 'creator'])) {
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
        $_user = User::find(Auth::user()->id);
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($_user->id),
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class)->ignore($_user->id),
            ],
            // password is optional
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'bio' => ['nullable', 'string', 'max:500'],
            'external_link' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'payout_method' => ['nullable', 'string', 'in:paypal,bank_transfer'],
            'payout_details' => [
                'nullable',
                'required_with:payout_method',
                'string',
                'max:1000',
                function ($attribute, $value, $fail) {
                    if ($this->input('payout_method') === 'paypal') {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $fail(__('For PayPal, the payout details must be a valid email address.'));
                        }
                    } else if ($this->input('payout_method') === 'bank_transfer') {
                        if (strlen($value) < 15) {
                            $fail(__('For Bank Transfer, please provide complete bank details (e.g., Bank Name, Account Holder Name, IBAN/Account Number, and Swift/BIC code).'));
                        }
                    }
                }
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('The name is required.'),
            'name.string' => __('The name must be a string.'),
            'name.max' => __('The name must be less than 255 characters.'),
            'email.required' => __('The email is required.'),
            'email.string' => __('The email must be a string.'),
            'email.lowercase' => __('The email must be in lowercase.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.max' => __('The email must be less than 255 characters.'),
            'email.unique' => __('The email must be unique.'),
            'username.required' => __('The username is required.'),
            'username.string' => __('The username must be a string.'),
            'username.max' => __('The username must be less than 255 characters.'),
            'username.unique' => __('The username must be unique.'),
            'password.required' => __('The password is required.'),
            'password.string' => __('The password must be a string.'),
            'password.min' => __('The password must be at least 8 characters.'),
            'password.confirmed' => __('The password confirmation does not match.'),
            'bio.max' => __('The bio must be less than 500 characters.'),
            'external_link.max' => __('The external link must be less than 255 characters.'),
            'external_link.url' => __('The external link must be a valid URL.'),
        ];
    }
}
