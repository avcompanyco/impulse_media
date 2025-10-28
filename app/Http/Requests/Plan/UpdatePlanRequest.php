<?php

namespace App\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Enums\Plan\BillingPeriod;

class UpdatePlanRequest extends FormRequest
{
    /**
     * Determine if the plan is authorized to make this request.
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
        $billingPeriods = '';
        foreach (BillingPeriod::cases() as $billingPeriod) {
            $billingPeriods .= $billingPeriod->value . ',';
        }
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_period' => ['required', 'string', 'in:' . $billingPeriods],
            'free_days_trial' => ['required', 'integer', 'min:0'],
            'is_unlimited_content' => ['nullable', 'boolean'],
            'movies_upload_count' => ['nullable', 'integer', 'min:0'],
            'series_upload_count' => ['nullable', 'integer', 'min:0'],
            'shorts_upload_count' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
