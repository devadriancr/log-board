<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * Unchecked checkboxes are not sent by the browser, so they are converted to false here.
     * The base URL is saved without a trailing slash.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'base_url' => rtrim(trim((string) $this->input('base_url')), '/'),
            'requires_auth' => $this->boolean('requires_auth'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('applications', 'name')->ignore($this->route('application')),
            ],
            'base_url' => [
                'required',
                'string',
                'max:255',
                'regex:/^https?:\/\/\S+$/i',
                Rule::unique('applications', 'base_url')->ignore($this->route('application')),
            ],
            'requires_auth' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get the translated names of the fields for the error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return __('applications.attributes');
    }

    /**
     * Get the custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'base_url.regex' => __('applications.validation.base_url'),
        ];
    }
}
