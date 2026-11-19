<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\WhatsappNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SaveUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('branch_selection_present') && ! $this->exists('branch_ids')) {
            $this->merge(['branch_ids' => []]);
        }
        // Branch Admin cannot choose locations: new accounts join its branch and
        // existing permissions stay as Super Admin configured them (DEC-061).
        if (! $this->user()->isSuperAdmin()) {
            $input = $this->except(['branch_ids', 'branch_selection_present']);
            if (! $this->route('user')) {
                $input['branch_ids'] = array_filter([$this->user()->managedBranchId()]);
            }
            $this->replace($input);
        }
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
        if ($this->has('whatsapp_number') && (is_string($this->input('whatsapp_number')) || $this->input('whatsapp_number') === null)) {
            $this->merge(['whatsapp_number' => WhatsappNumber::normalize($this->input('whatsapp_number'))]);
        }
    }

    public function authorize(): bool
    {
        $record = $this->route('user');

        return $this->user()->can('access-admin') && (! $record || $this->user()->can('manage-user', $record));
    }

    public function rules(): array
    {
        $record = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'string', 'email', 'max:255', function ($attribute, $value, $fail) use ($record) {
                if (User::whereRaw('lower(btrim(email)) = ?', [$value])->when($record, fn ($query) => $query->whereKeyNot($record->id))->exists()) {
                    $fail('Email sudah digunakan oleh akun lain.');
                }
            }],
            'whatsapp_number' => ['bail', 'sometimes', 'nullable', 'string', 'regex:/^\+[1-9][0-9]{7,14}$/'],
            'role' => ['required', Rule::in($this->user()->assignableRoles())],
            'organizational_unit_id' => ['bail', 'nullable', 'integer', 'exists:organizational_units,id'],
            'is_active' => ['required', 'boolean'],
            'branch_ids' => ['sometimes', 'array'],
            'branch_ids.*' => ['bail', 'required', 'integer', 'distinct', 'exists:branches,id'],
            'password' => [$record ? 'nullable' : 'required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->hasAny(['role', 'branch_ids', 'branch_ids.*']) || $this->input('role') !== User::ROLE_ADMIN) {
                return;
            }
            // Without the field, an edit keeps the account's current locations.
            $branches = $this->exists('branch_ids') ? (array) $this->input('branch_ids')
                : ($this->route('user')?->branches()->pluck('branches.id')->all() ?? []);
            if (count($branches) !== 1) {
                $validator->errors()->add('branch_ids', 'Admin harus memiliki tepat satu kantor / cabang yang dikelola.');
            }
        });
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama', 'whatsapp_number' => 'nomor WhatsApp', 'organizational_unit_id' => 'unit kerja',
            'is_active' => 'status aktif',
            'branch_ids' => 'izin lokasi', 'branch_ids.*' => 'lokasi',
        ];
    }
}
