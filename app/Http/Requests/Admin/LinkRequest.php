<?php

namespace App\Http\Requests\Admin;

use App\Models\Link;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LinkRequest extends FormRequest
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
     * - Unchecked checkboxes are not sent by the browser, so they are converted to false.
     * - The path always starts with a slash.
     * - Empty option rows are dropped, and the label defaults to the value.
     * - Options only make sense when the path has a {param} placeholder.
     */
    protected function prepareForValidation(): void
    {
        $path = '/'.ltrim(trim((string) $this->input('path')), '/');

        $options = collect($this->input('options', []))
            ->filter(fn ($option) => is_array($option))
            ->map(fn (array $option) => [
                'value' => trim((string) ($option['value'] ?? '')),
                'label' => trim((string) ($option['label'] ?? '')),
                'label_en' => trim((string) ($option['label_en'] ?? '')) ?: null,
                'label_ja' => trim((string) ($option['label_ja'] ?? '')) ?: null,
            ])
            ->filter(fn (array $option) => $option['value'] !== '' || $option['label'] !== '')
            ->map(fn (array $option) => [
                'value' => $option['value'],
                'label' => $option['label'] !== '' ? $option['label'] : $option['value'],
                'label_en' => $option['label_en'],
                'label_ja' => $option['label_ja'],
            ])
            ->values()
            ->all();

        $this->merge([
            'path' => $path,
            'options' => str_contains($path, '{param}') ? $options : [],
            'show_description' => $this->boolean('show_description'),
            'open_in_new_tab' => $this->boolean('open_in_new_tab'),
            'show_preview' => $this->boolean('show_preview'),
            'preview_size' => $this->input('preview_size') ?: 'fill',
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
            'card_id' => ['required', 'integer', Rule::exists('cards', 'id')->where('type', 'links')],
            'application_id' => ['required', 'integer', 'exists:applications,id'],
            'label' => ['required', 'string', 'max:255'],
            'label_en' => ['nullable', 'string', 'max:255'],
            'label_ja' => ['nullable', 'string', 'max:255'],
            'path' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'description_en' => ['nullable', 'string', 'max:1000'],
            'description_ja' => ['nullable', 'string', 'max:1000'],
            'show_description' => ['boolean'],
            'open_in_new_tab' => ['boolean'],
            'show_preview' => ['boolean'],
            'preview_size' => ['required', Rule::in(Link::PREVIEW_SIZES)],
            'is_active' => ['boolean'],
            'options' => [
                Rule::requiredIf(fn () => str_contains((string) $this->input('path'), '{param}')),
                'array',
            ],
            'options.*.value' => ['required', 'string', 'max:255', 'distinct'],
            'options.*.label' => ['required', 'string', 'max:255'],
            'options.*.label_en' => ['nullable', 'string', 'max:255'],
            'options.*.label_ja' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get the translated names of the fields for the error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return __('links.attributes');
    }
}
