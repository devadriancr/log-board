<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Unchecked checkboxes are not sent by the browser, so they are converted to false here.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in(array_keys(self::assignableRoles()))],
            // Required for a new user; for an existing one only when it is to be changed.
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * The roles the person can give: only a super admin can make another super admin.
     *
     * @return array<string, string> role name => text
     */
    public static function assignableRoles(): array
    {
        return Role::orderBy('id')->pluck('name')
            ->reject(fn (string $name) => $name === 'super-admin' && ! request()->user()->isSuperAdmin())
            ->mapWithKeys(fn (string $name) => [$name => __('roles.names.'.$name)])
            ->all();
    }

    /**
     * Get the translated names of the fields for the error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return __('users.attributes');
    }
}
