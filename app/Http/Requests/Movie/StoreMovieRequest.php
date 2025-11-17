<?php

namespace App\Http\Requests\Movie;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreMovieRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'movie_video' => 'required|file|mimes:mp4,webm,avi,mov,wmv,flv,mkv|max:512000', // 500MB max
            'trailer_video' => 'required|file|mimes:mp4,webm,avi,mov,wmv,flv,mkv|max:512000', // 500MB max
            'horizontal_image' => 'required|file|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:10240', // 10MB max
            'vertical_image' => 'required|file|mimes:jpeg,jpg,png,gif,bmp,webp,svg|max:10240', // 10MB max
            'category_id' => 'required|integer|exists:categories,id',
            'subcategory_id' => 'required|integer|exists:subcategories,id',
        ];
    }
}
