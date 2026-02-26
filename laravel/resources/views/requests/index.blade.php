@extends('layouts.admin')

@section('title', 'Solicitudes')
@section('page-title', 'Solicitudes')
@section('breadcrumb', 'Inicio / Solicitudes')

@php
    $statusStyles = [
        'BORRADOR' => 'secondary',
        'OBSERVADA' => 'warning',
        'ENVIADA' => 'primary',
        'EN_GESTION_PERSONAS' => 'info',
        'EN_RRHH' => 'info',
        'RECHAZADA' => 'danger',
        'EN_TRAMITACION_CONTRATO' => 'dark',
        'FINALIZADA' => 'success',
    ];
@endphp

@section('content')
    @if(session('status'))
        <div class="alert alert-success shadow-sm border-0" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-2">Solicitudes de Reemplazo</h1>
                <p class="text-muted mb-0">Listado de solicitudes reales cargadas desde la base de datos.</p>
            </div>
            @can('create', \App\Models\Request::class)
                <a href="{{ route('requests.create') }}" class="btn btn-primary">Nueva Solicitud</a>
            @endcan
        </div>
    </section>

    <section id="solicitudes-panel" class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead class="table-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Servicio</th>
                        <th scope="col">Creada</th>
                        <th scope="col" class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($requests as $request)
                        @php
                            $status = $request->status->value;
                            $canSend = auth()->user()->can('send', $request);
                        @endphp
                        <tr id="request-row-{{ $request->id }}">
                            <td class="fw-semibold">#{{ $request->id }}</td>
                            <td>
                                <span id="status-badge-{{ $request->id }}" class="badge text-bg-{{ $statusStyles[$status] ?? 'secondary' }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td>{{ $request->service?->name ?? 'Sin servicio' }}</td>
                            <td>{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                @if($canSend)
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary btn-send"
                                        data-request-id="{{ $request->id }}"
                                    >
                                        Enviar
                                    </button>
                                @else
                                    <span class="text-muted small">Sin acciones disponibles</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Sin solicitudes por mostrar.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const statusClassMap = {
                BORRADOR: 'text-bg-secondary',
                OBSERVADA: 'text-bg-warning',
                ENVIADA: 'text-bg-primary',
                EN_GESTION_PERSONAS: 'text-bg-info',
                EN_RRHH: 'text-bg-info',
                RECHAZADA: 'text-bg-danger',
                EN_TRAMITACION_CONTRATO: 'text-bg-dark',
                FINALIZADA: 'text-bg-success',
            };

            const showToast = (message, type = 'success') => {
                window.dispatchEvent(new CustomEvent('admin-toast', {
                    detail: { message, type },
                }));
            };

            document.querySelectorAll('.btn-send').forEach((button) => {
                button.addEventListener('click', async () => {
                    const requestId = button.dataset.requestId;
                    button.disabled = true;

                    try {
                        const response = await fetch(`/requests/${requestId}/actions/send`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({}),
                        });

                        const result = await response.json();

                        if (!response.ok || !result.ok) {
                            throw new Error(result.message || 'No fue posible enviar la solicitud.');
                        }

                        const badge = document.getElementById(`status-badge-${requestId}`);

                        if (badge) {
                            badge.className = `badge ${statusClassMap[result.data.status] ?? 'text-bg-secondary'}`;
                            badge.textContent = result.data.status;
                        }

                        button.remove();
                        showToast(result.message);
                    } catch (error) {
                        button.disabled = false;
                        showToast(error.message || 'Error inesperado al enviar.', 'error');
                    }
                });
            });
        });
    </script>
@endpush
