<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class BoardDesignRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cards' => ['required', 'array', 'max:200'],
            'cards.*.id' => ['required', 'integer', 'distinct'],
            'cards.*.x' => ['required', 'integer', 'min:0', 'max:11'],
            'cards.*.y' => ['required', 'integer', 'min:0', 'max:60000'],
            'cards.*.w' => ['required', 'integer', 'min:1', 'max:12'],
            'cards.*.h' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }

    /**
     * Make sure no card goes past the 12th column.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                foreach ($this->input('cards', []) as $index => $card) {
                    if (((int) ($card['x'] ?? 0)) + ((int) ($card['w'] ?? 0)) > 12) {
                        $validator->errors()->add("cards.$index.x", __('design.messages.overflow'));
                    }
                }
            },
        ];
    }
}
