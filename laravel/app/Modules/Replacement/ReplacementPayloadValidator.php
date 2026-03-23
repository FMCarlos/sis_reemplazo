<?php

namespace App\Modules\Replacement;

use Illuminate\Support\Facades\Validator;

class ReplacementPayloadValidator
{
    public function validate(array $payload): array
    {
        return Validator::make($payload, $this->rules())->validate();
    }

    protected function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'max:255'],
            'subject_employee_id' => ['required', 'integer', 'exists:employees,id'],
            'replacement_is_external' => ['required', 'boolean'],
            'replacement_employee_id' => [
                'nullable',
                'integer',
                'exists:employees,id',
                'required_if:replacement_is_external,false',
                'exclude_if:replacement_is_external,true',
                'different:subject_employee_id',
            ],
            'replacement_full_name' => [
                'nullable',
                'string',
                'max:255',
                'required_if:replacement_is_external,true',
                'exclude_unless:replacement_is_external,true',
            ],
            'replacement_rut' => ['nullable', 'string', 'max:20', 'exclude_unless:replacement_is_external,true'],
            'replacement_dv' => ['nullable', 'string', 'size:1', 'exclude_unless:replacement_is_external,true'],
            'replacement_profession' => ['nullable', 'string', 'max:255', 'exclude_unless:replacement_is_external,true'],
            'replacement_specialty' => ['nullable', 'string', 'max:255', 'exclude_unless:replacement_is_external,true'],
            'replacement_notes' => ['nullable', 'string', 'max:2000', 'exclude_unless:replacement_is_external,true'],
            'absence_type_id' => ['nullable', 'integer', 'exists:absence_types,id'],
            'absence_detail' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'fecha_inicio' => ['sometimes', 'date'],
            'fecha_fin' => ['sometimes', 'date'],
            'nombre_reemplazo' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
