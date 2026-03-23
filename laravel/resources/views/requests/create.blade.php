@extends('layouts.admin')

@section('title', isset($submission) ? 'Editar borrador de reemplazo' : 'Nuevo formulario de reemplazo')
@section('page-title', 'Nuevo formulario de reemplazo')
@section('breadcrumb', 'Inicio / Solicitudes / Nueva')

@section('content')
    <section class="card border-0 shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-2">{{ isset($submission) ? 'Editar borrador de reemplazo' : 'Crear formulario de reemplazo' }}</h1>
            <p class="text-muted">Flujo simple: guardar en borrador, enviar a RRHH, resolver y consultar trazabilidad.</p>

            <div id="request-form-feedback" class="alert d-none" role="alert"></div>

            <form method="POST" action="{{ isset($submission) ? route('requests.update', $submission) : route('requests.store') }}" class="row g-3" id="request-create-form" novalidate data-method="{{ isset($submission) ? 'PUT' : 'POST' }}">
                @if(isset($submission))
                    @method('PUT')
                @endif
                @csrf

                <div class="col-12">
                    <h2 class="h6 text-uppercase text-muted mb-2">Funcionario a reemplazar</h2>
                </div>

                <div class="col-12">
                    <label for="subject_employee_id" class="form-label">Funcionario titular</label>
                    <select
                        id="subject_employee_id"
                        name="subject_employee_id"
                        class="form-select @error('subject_employee_id') is-invalid @enderror"
                        required
                    >
                        <option value="">Selecciona funcionario</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('subject_employee_id', data_get($payload, 'subject_employee.id')) == $employee->id)>
                                {{ $employee->full_name }} · {{ $employee->rut }}-{{ $employee->dv }}
                                @if($employee->unidad)
                                    · {{ $employee->unidad }}
                                @endif
                                @if($employee->profesion)
                                    · {{ $employee->profesion }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" data-error-for="subject_employee_id">
                        @error('subject_employee_id'){{ $message }}@enderror
                    </div>
                </div>

                <div class="col-12">
                    <hr class="my-2">
                    <h2 class="h6 text-uppercase text-muted mb-2">Reemplazante</h2>
                </div>

                <div class="col-md-4">
                    <label for="replacement_is_external" class="form-label">Origen reemplazante</label>
                    <select
                        id="replacement_is_external"
                        name="replacement_is_external"
                        class="form-select @error('replacement_is_external') is-invalid @enderror"
                        required
                    >
                        <option value="0" @selected(old('replacement_is_external', data_get($payload, 'replacement.is_external') ? '1' : '0') === '0')>Interno</option>
                        <option value="1" @selected(old('replacement_is_external', data_get($payload, 'replacement.is_external') ? '1' : '0') === '1')>Externo</option>
                    </select>
                    <div class="invalid-feedback" data-error-for="replacement_is_external">
                        @error('replacement_is_external'){{ $message }}@enderror
                    </div>
                </div>

                <div id="replacement-internal-fields" class="col-12 row g-3">
                    <div class="col-12">
                        <label for="replacement_employee_id" class="form-label">Funcionario reemplazante interno</label>
                        <select
                            id="replacement_employee_id"
                            name="replacement_employee_id"
                            class="form-select @error('replacement_employee_id') is-invalid @enderror"
                        >
                            <option value="">Selecciona reemplazante interno</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(old('replacement_employee_id', data_get($payload, 'replacement.employee_id')) == $employee->id)>
                                    {{ $employee->full_name }} · {{ $employee->rut }}-{{ $employee->dv }}
                                    @if($employee->unidad)
                                        · {{ $employee->unidad }}
                                    @endif
                                    @if($employee->profesion)
                                        · {{ $employee->profesion }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" data-error-for="replacement_employee_id">
                            @error('replacement_employee_id'){{ $message }}@enderror
                        </div>
                    </div>
                </div>

                <div id="replacement-external-fields" class="col-12 row g-3 d-none">
                    <div class="col-md-6">
                        <label for="replacement_full_name" class="form-label">Nombre completo reemplazante externo</label>
                        <input
                            type="text"
                            id="replacement_full_name"
                            name="replacement_full_name"
                            value="{{ old('replacement_full_name', data_get($payload, 'replacement.full_name')) }}"
                            class="form-control @error('replacement_full_name') is-invalid @enderror"
                            placeholder="Nombre completo"
                        >
                        <div class="invalid-feedback" data-error-for="replacement_full_name">
                            @error('replacement_full_name'){{ $message }}@enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="replacement_rut" class="form-label">RUT</label>
                        <input
                            type="text"
                            id="replacement_rut"
                            name="replacement_rut"
                            value="{{ old('replacement_rut', data_get($payload, 'replacement.rut')) }}"
                            class="form-control @error('replacement_rut') is-invalid @enderror"
                            placeholder="12345678"
                        >
                        <div class="invalid-feedback" data-error-for="replacement_rut">
                            @error('replacement_rut'){{ $message }}@enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="replacement_dv" class="form-label">DV</label>
                        <input
                            type="text"
                            id="replacement_dv"
                            name="replacement_dv"
                            value="{{ old('replacement_dv', data_get($payload, 'replacement.dv')) }}"
                            class="form-control @error('replacement_dv') is-invalid @enderror"
                            maxlength="1"
                            placeholder="K"
                        >
                        <div class="invalid-feedback" data-error-for="replacement_dv">
                            @error('replacement_dv'){{ $message }}@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="replacement_profession" class="form-label">Profesión</label>
                        <input
                            type="text"
                            id="replacement_profession"
                            name="replacement_profession"
                            value="{{ old('replacement_profession', data_get($payload, 'replacement.profession')) }}"
                            class="form-control @error('replacement_profession') is-invalid @enderror"
                        >
                        <div class="invalid-feedback" data-error-for="replacement_profession">
                            @error('replacement_profession'){{ $message }}@enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="replacement_specialty" class="form-label">Especialidad</label>
                        <input
                            type="text"
                            id="replacement_specialty"
                            name="replacement_specialty"
                            value="{{ old('replacement_specialty', data_get($payload, 'replacement.specialty')) }}"
                            class="form-control @error('replacement_specialty') is-invalid @enderror"
                        >
                        <div class="invalid-feedback" data-error-for="replacement_specialty">
                            @error('replacement_specialty'){{ $message }}@enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="replacement_notes" class="form-label">Notas del reemplazante externo</label>
                        <textarea
                            id="replacement_notes"
                            name="replacement_notes"
                            class="form-control @error('replacement_notes') is-invalid @enderror"
                            rows="2"
                        >{{ old('replacement_notes', data_get($payload, 'replacement.notes')) }}</textarea>
                        <div class="invalid-feedback" data-error-for="replacement_notes">
                            @error('replacement_notes'){{ $message }}@enderror
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <hr class="my-2">
                    <h2 class="h6 text-uppercase text-muted mb-2">Ausentismo</h2>
                </div>

                <div class="col-md-6">
                    <label for="absence_type_id" class="form-label">Tipo de ausencia</label>
                    <select
                        id="absence_type_id"
                        name="absence_type_id"
                        class="form-select @error('absence_type_id') is-invalid @enderror"
                    >
                        <option value="">Selecciona tipo (opcional)</option>
                        @foreach ($absenceTypes as $absenceType)
                            <option value="{{ $absenceType->id }}" @selected(old('absence_type_id', data_get($payload, 'absence.type_id')) == $absenceType->id)>
                                {{ $absenceType->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" data-error-for="absence_type_id">
                        @error('absence_type_id'){{ $message }}@enderror
                    </div>
                </div>

                <div class="col-12">
                    <label for="absence_detail" class="form-label">Detalle ausentismo</label>
                    <textarea
                        id="absence_detail"
                        name="absence_detail"
                        class="form-control @error('absence_detail') is-invalid @enderror"
                        rows="2"
                        placeholder="Detalle adicional (opcional)"
                    >{{ old('absence_detail', data_get($payload, 'absence.detail')) }}</textarea>
                    <div class="invalid-feedback" data-error-for="absence_detail">
                        @error('absence_detail'){{ $message }}@enderror
                    </div>
                </div>

                <div class="col-12">
                    <hr class="my-2">
                    <h2 class="h6 text-uppercase text-muted mb-2">Fechas y motivo</h2>
                </div>

                <div class="col-md-6">
                    <label for="start_date" class="form-label">Fecha inicio</label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        value="{{ old('start_date', data_get($payload, 'period.start_date')) }}"
                        class="form-control @error('start_date') is-invalid @enderror"
                        required
                    >
                    <div class="invalid-feedback" data-error-for="start_date">
                        @error('start_date'){{ $message }}@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="end_date" class="form-label">Fecha fin</label>
                    <input
                        type="date"
                        id="end_date"
                        name="end_date"
                        value="{{ old('end_date', data_get($payload, 'period.end_date')) }}"
                        class="form-control @error('end_date') is-invalid @enderror"
                        required
                    >
                    <div class="invalid-feedback" data-error-for="end_date">
                        @error('end_date'){{ $message }}@enderror
                    </div>
                </div>

                <div class="col-12">
                    <label for="motivo" class="form-label">Motivo</label>
                    <input
                        type="text"
                        id="motivo"
                        name="motivo"
                        value="{{ old('motivo', data_get($payload, 'motivo')) }}"
                        class="form-control @error('motivo') is-invalid @enderror"
                        placeholder="Ej: Licencia médica"
                        required
                    >
                    <div class="invalid-feedback" data-error-for="motivo">
                        @error('motivo'){{ $message }}@enderror
                    </div>
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end mt-3">
                    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="request-create-submit">{{ isset($submission) ? 'Actualizar borrador' : 'Guardar borrador' }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.getElementById('request-create-form');
            if (!form) {
                return;
            }

            const submitButton = document.getElementById('request-create-submit');
            const feedback = document.getElementById('request-form-feedback');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const replacementModeInput = document.getElementById('replacement_is_external');
            const replacementInternalFields = document.getElementById('replacement-internal-fields');
            const replacementExternalFields = document.getElementById('replacement-external-fields');
            const replacementEmployeeInput = document.getElementById('replacement_employee_id');
            const replacementFullNameInput = document.getElementById('replacement_full_name');

            const clearValidationErrors = () => {
                form.querySelectorAll('input, select, textarea').forEach((element) => {
                    element.classList.remove('is-invalid');
                });

                form.querySelectorAll('[data-error-for]').forEach((errorContainer) => {
                    errorContainer.textContent = '';
                });
            };

            const showFeedback = (message, type = 'danger') => {
                if (!feedback) {
                    return;
                }

                if (!message) {
                    feedback.className = 'alert d-none';
                    feedback.textContent = '';
                    return;
                }

                feedback.className = `alert alert-${type}`;
                feedback.textContent = message;
            };

            const syncReplacementMode = () => {
                const isExternal = replacementModeInput?.value === '1';

                replacementInternalFields?.classList.toggle('d-none', isExternal);
                replacementExternalFields?.classList.toggle('d-none', !isExternal);

                if (replacementEmployeeInput) {
                    replacementEmployeeInput.required = !isExternal;
                }

                if (replacementFullNameInput) {
                    replacementFullNameInput.required = isExternal;
                }
            };

            replacementModeInput?.addEventListener('change', syncReplacementMode);
            syncReplacementMode();

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearValidationErrors();
                showFeedback('');
                syncReplacementMode();

                if (submitButton) {
                    submitButton.disabled = true;
                }

                const formData = new FormData(form);
                const payload = Object.fromEntries(formData.entries());
                payload._method = form.dataset.method ?? 'POST';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    const result = await response.json();

                    if (response.ok && result.ok) {
                        showFeedback(result.message ?? 'Borrador guardado correctamente.', 'success');
                        window.setTimeout(() => {
                            if (result?.data?.show_url) {
                                window.location.href = result.data.show_url;
                                return;
                            }

                            form.reset();
                            syncReplacementMode();
                        }, 600);
                        return;
                    }

                    if (response.status === 422 && result.errors) {
                        Object.entries(result.errors).forEach(([field, messages]) => {
                            const input = form.querySelector(`[name="${field}"]`);
                            const errorContainer = form.querySelector(`[data-error-for="${field}"]`);

                            if (input) {
                                input.classList.add('is-invalid');
                            }

                            if (errorContainer) {
                                errorContainer.textContent = Array.isArray(messages) ? messages[0] : String(messages);
                            }
                        });

                        showFeedback(result.message ?? 'Revisa los campos del formulario.', 'danger');
                        return;
                    }

                    showFeedback(result.message ?? 'No fue posible crear la solicitud.', 'danger');
                } catch (error) {
                    showFeedback('Ocurrió un error de red al guardar la solicitud.', 'danger');
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                }
            });
        })();
    </script>
@endpush
