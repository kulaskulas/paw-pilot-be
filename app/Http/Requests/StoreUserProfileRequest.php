<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NoHtml;

class StoreUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255', new NoHtml()],
            'last_name' => ['required', 'string', 'max:255', new NoHtml()],
            'phone_number' => ['nullable', 'string', 'max:20', new NoHtml()],
            'address' => ['nullable', 'string', 'max:500', new NoHtml()],
        ];
    }
}
