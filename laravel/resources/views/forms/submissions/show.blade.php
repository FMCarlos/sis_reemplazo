@extends('layouts.admin')

@section('title', 'Detalle de envío')

@section('content')
    <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Envío #{{ $submission->id }}</h1>
            <p class="text-muted mb-0">Detalle del primer formulario modular de reemplazo operando sobre el nuevo núcleo institucional.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('forms.index') }}" class="btn btn-outline-secondary">Catálogo</a>
            <a href="{{ route('forms.submissions.index') }}" class="btn btn-outline-primary">Volver al listado</a>
            @can('downloadPdf', $submission)
                @if ($submission->pdf_path)
                    <a href="{{ route('forms.submissions.pdf', $submission) }}" class="btn btn-success">Descargar PDF</a>
                @endif
            @endcan
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-8">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Información general</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Tipo de formulario</small>
                            <span class="fw-semibold">{{ $submission->formType?->name ?? 'Sin tipo' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Código</small>
                            <span>{{ $submission->formType?->code ?? 'Sin código' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Estado</small>
                            <span class="badge {{ $submission->status?->badgeClass() ?? 'bg-dark' }}">{{ $submission->status?->label() ?? 'SIN ESTADO' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Enviado por</small>
                            <span>{{ $submission->submitter?->name ?? 'Usuario no disponible' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Fecha de envío</small>
                            <span>{{ $submission->submitted_at?->format('Y-m-d H:i') ?? 'Pendiente' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">PDF asociado</small>
                            <span>{{ $submission->pdf_path ?: 'No generado aún' }}</span>
                        </div>
                    </div>

                    @php($payload = $submission->payload_json ?? [])

                    <hr class="my-4">

                    <div class="d-flex flex-wrap gap-2">
                        @can('update', $submission)
                            <a href="{{ route('requests.edit', $submission) }}" class="btn btn-outline-secondary">Editar borrador</a>
                        @endcan

                        @can('submit', $submission)
                            <button type="button" class="btn btn-primary" data-workflow-action="submit" data-action-url="{{ route('forms.submissions.submit', $submission) }}">Enviar a RRHH</button>
                        @endcan

                        @can('approve', $submission)
                            <button type="button" class="btn btn-success" data-workflow-action="approve" data-action-url="{{ route('forms.submissions.approve', $submission) }}">Aprobar RRHH</button>
                        @endcan

                        @can('reject', $submission)
                            <button type="button" class="btn btn-outline-danger" data-workflow-action="reject" data-action-url="{{ route('forms.submissions.reject', $submission) }}">Rechazar RRHH</button>
                        @endcan
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Funcionario titular</small>
                            <span>{{ data_get($payload, 'subject_employee.full_name', 'No informado') }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Reemplazante</small>
                            <span>{{ data_get($payload, 'replacement.full_name', 'No informado') }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Origen reemplazante</small>
                            <span>{{ data_get($payload, 'replacement.is_external') ? 'Externo' : 'Interno' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Tipo de ausencia</small>
                            <span>{{ data_get($payload, 'absence.type_name', 'No informado') }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Fecha inicio</small>
                            <span>{{ data_get($payload, 'period.start_date', 'No informada') }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Fecha fin</small>
                            <span>{{ data_get($payload, 'period.end_date', 'No informada') }}</span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Motivo</small>
                            <span>{{ data_get($payload, 'motivo', 'Sin motivo') }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Payload JSON</h2>
                </div>
                <div class="card-body">
                    <pre class="bg-light border rounded p-3 small mb-0" style="white-space: pre-wrap;">{{ json_encode($submission->payload_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </section>

            <section class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Trazabilidad simple</h2>
                </div>
                <div class="card-body">
                    @forelse ($submission->actions->sortByDesc('created_at') as $action)
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between gap-3 mb-2">
                                <span class="fw-semibold">{{ \Illuminate\Support\Str::headline($action->action) }}</span>
                                <small class="text-muted">{{ $action->created_at?->format('Y-m-d H:i') }}</small>
                            </div>
                            <div class="small text-muted">Usuario: {{ $action->user?->name ?? 'Sistema' }}</div>
                            @if (!empty($action->payload_json))
                                <pre class="bg-white border rounded p-2 small mt-2 mb-0" style="white-space: pre-wrap;">{{ json_encode($action->payload_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted small">Aún no existen eventos registrados para este envío.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

            document.querySelectorAll('[data-workflow-action]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const action = button.dataset.workflowAction;
                    const url = button.dataset.actionUrl;
                    const requiresComment = action === 'approve' || action === 'reject';
                    const comment = requiresComment ? (window.prompt('Comentario opcional para RRHH:', '') ?? '') : '';

                    button.disabled = true;

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ comment }),
                        });

                        const result = await response.json();

                        if (response.ok && result.ok) {
                            window.location.reload();
                            return;
                        }

                        window.alert(result.message ?? 'No fue posible aplicar la acción.');
                    } catch (error) {
                        window.alert('Ocurrió un error de red al aplicar la acción.');
                    } finally {
                        button.disabled = false;
                    }
                });
            });
        })();
    </script>
@endpush
