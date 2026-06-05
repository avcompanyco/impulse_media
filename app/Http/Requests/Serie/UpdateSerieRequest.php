<?php

namespace App\Http\Requests\Serie;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSerieRequest extends FormRequest
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
        $minPpvPrice = (float) \App\Models\Setting::get('min_ppv_price', 0.99);
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'category_id' => 'required|integer|exists:categories,id',
            'subcategory_id' => 'required|integer|exists:subcategories,id',
            'ppv_price' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($minPpvPrice) {
                    $val = (float)$value;
                    if ($val !== 0.00 && $val < $minPpvPrice) {
                        $fail(__('The price must be either 0 (free) or at least $' . number_format($minPpvPrice, 2)));
                    }
                }
            ],
            'allow_membership' => 'required|boolean',
        ];
    }
}
