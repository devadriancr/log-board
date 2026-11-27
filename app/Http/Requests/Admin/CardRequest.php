<?php

namespace App\Http\Requests\Admin;

use App\Models\Card;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CardRequest extends FormRequest
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
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'slide_seconds' => $this->integer('slide_seconds') ?: 8,
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
            'board_id' => ['required', 'integer', 'exists:boards,id'],
            'title' => [Rule::requiredIf(fn () => $this->input('type') !== Card::TYPE_TEXT), 'nullable', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'title_ja' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', Rule::in(Card::icons())],
            'color' => ['nullable', Rule::in(array_keys(Card::COLORS))],
            'type' => ['required', Rule::in([Card::TYPE_LINKS, Card::TYPE_TEXT])],
            'content' => [
                Rule::requiredIf(fn () => $this->input('type') === Card::TYPE_TEXT),
                'nullable',
                'array',
                'max:20',
                function (string $attribute, mixed $slides, $fail) {
                    if ($this->input('type') === Card::TYPE_TEXT && collect($slides)->every(fn ($html) => Card::isBlankSlide($html))) {
                        $fail(__('validation.required', ['attribute' => __('cards.attributes.content')]));
                    }
                },
            ],
            'content.*' => ['nullable', 'string', 'max:500000'],
            'content_en' => ['nullable', 'array', 'max:20'],
            'content_en.*' => ['nullable', 'string', 'max:500000'],
            'content_ja' => ['nullable', 'array', 'max:20'],
            'content_ja.*' => ['nullable', 'string', 'max:500000'],
            'slide_seconds' => ['required', 'integer', 'min:3', 'max:60'],
            'grid_w' =>['required', 'integer', 'min:1', 'max:12'],
            'grid_h' => ['required', 'integer', 'min:1', 'max:20'],
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
        return __('cards.attributes');
    }
}
