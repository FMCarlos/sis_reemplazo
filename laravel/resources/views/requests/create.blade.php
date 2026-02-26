@extends('layouts.admin')

@section('title', 'Nueva Solicitud')
@section('page-title', 'Nueva Solicitud')
@section('breadcrumb', 'Inicio / Solicitudes / Nueva')

@section('content')
    <section class="card border-0 shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-4">Crear solicitud de reemplazo</h1>

            <div id="request-form-feedback" class="alert d-none" role="alert"></div>

            <form method="POST" action="{{ route('requests.store') }}" class="row g-3" id="request-create-form" novalidate>
                @csrf

                <div class="col-12">
                    <label for="motivo" class="form-label">Motivo</label>
                    <input
                        type="text"
                        id="motivo"
                        name="motivo"
                        value="{{ old('motivo') }}"
                        class="form-control @error('motivo') is-invalid @enderror"
                        placeholder="Ej: Licencia médica"
                        required
                    >
                    <div class="invalid-feedback" data-error-for="motivo">
                        @error('motivo'){{ $message }}@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                    <input
                        type="date"
                        id="fecha_inicio"
                        name="fecha_inicio"
                        value="{{ old('fecha_inicio') }}"
                        class="form-control @error('fecha_inicio') is-invalid @enderror"
                        required
                    >
                    <div class="invalid-feedback" data-error-for="fecha_inicio">
                        @error('fecha_inicio'){{ $message }}@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="fecha_fin" class="form-label">Fecha fin</label>
                    <input
                        type="date"
                        id="fecha_fin"
                        name="fecha_fin"
                        value="{{ old('fecha_fin') }}"
                        class="form-control @error('fecha_fin') is-invalid @enderror"
                        required
                    >
                    <div class="invalid-feedback" data-error-for="fecha_fin">
                        @error('fecha_fin'){{ $message }}@enderror
                    </div>
                </div>

                <div class="col-12">
                    <label for="nombre_reemplazo" class="form-label">Nombre reemplazo</label>
                    <input
                        type="text"
                        id="nombre_reemplazo"
                        name="nombre_reemplazo"
                        value="{{ old('nombre_reemplazo') }}"
                        class="form-control @error('nombre_reemplazo') is-invalid @enderror"
                        placeholder="Ej: Pendiente de definir"
                        required
                    >
                    <div class="invalid-feedback" data-error-for="nombre_reemplazo">
                        @error('nombre_reemplazo'){{ $message }}@enderror
                    </div>
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end mt-3">
                    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="request-create-submit">Guardar borrador</button>
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

            const clearValidationErrors = () => {
                form.querySelectorAll('input').forEach((input) => {
                    input.classList.remove('is-invalid');
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

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearValidationErrors();
                showFeedback('');

                if (submitButton) {
                    submitButton.disabled = true;
                }

                const formData = new FormData(form);
                const payload = Object.fromEntries(formData.entries());

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
                        showFeedback(result.message ?? 'Solicitud creada en borrador.', 'success');
                        form.reset();
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
