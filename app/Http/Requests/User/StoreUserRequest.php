<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

use App\Models\User;

class StoreUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'status' => ['required', 'string', 'in:active,suspended'],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'user_type' => ['required', 'string', 'in:spectator,creator,admin'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('The name field is required.'),
            'username.required' => __('The username field is required.'),
            'username.unique' => __('This username is already taken.'),
            'email.required' => __('The email field is required.'),
            'email.email' => __('Please enter a valid email address.'),
            'email.unique' => __('This email is already registered.'),
            'password.required' => __('The password field is required.'),
            'password.confirmed' => __('Password confirmation does not match.'),
            'status.required' => __('The status field is required.'),
            'status.in' => __('Invalid status selected.'),
            'plan_id.exists' => __('Selected plan does not exist.'),
            'trial_days.integer' => __('Trial days must be a valid number.'),
            'trial_days.min' => __('Trial days cannot be negative.'),
            'image.image' => __('The file must be an image.'),
            'image.mimes' => __('The image must be a file of type: jpeg, png, jpg, gif.'),
            'image.max' => __('The image size must not be greater than 2MB.'),
        ];
    }
}
