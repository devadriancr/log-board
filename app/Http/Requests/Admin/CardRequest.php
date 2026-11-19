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
            'icon' => ['nullable', 'string', Rule::in(Card::icons())],
            'color' => ['nullable', Rule::in(array_keys(Card::COLORS))],
            'type' => ['required', Rule::in([Card::TYPE_LINKS, Card::TYPE_TEXT])],
            'content' => [Rule::requiredIf(fn () => $this->input('type') === Card::TYPE_TEXT), 'nullable', 'string', 'max:500000'],
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
