@extends('layouts.admin')

@section('title', 'Solicitudes')
@section('page-title', 'Solicitudes')
@section('breadcrumb', 'Inicio / Solicitudes')

@section('content')
    @if(session('status'))
        <div class="alert alert-success shadow-sm border-0" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <section class="card border-0 shadow-sm mb-4" id="filters-card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h2 class="h6 mb-0">Filtros</h2>
            <button
                class="btn btn-sm btn-outline-secondary"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#requests-filters-collapse"
                aria-expanded="false"
                aria-controls="requests-filters-collapse"
                id="filters-toggle"
            >
                Mostrar/Ocultar filtros
            </button>
        </div>
        <div class="collapse" id="requests-filters-collapse">
            <div class="card-body">
                <form method="GET" action="{{ route('requests.index') }}" class="row g-3">
                    <input type="hidden" name="tab" value="{{ $activeTab }}">

                    <div class="col-md-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select name="estado" id="estado" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($availableStatuses as $status)
                                <option value="{{ $status }}" @selected(($filters['estado'] ?? null) === $status)>
                                    {{ $statusLabels[$status] ?? $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($canFilterService)
                        <div class="col-md-3">
                            <label for="servicio" class="form-label">Servicio</label>
                            <select name="servicio" id="servicio" class="form-select">
                                <option value="">Todos</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}" @selected((string) ($filters['servicio'] ?? '') === (string) $service->id)>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-{{ $canFilterService ? '2' : '3' }}">
                        <label for="created_from" class="form-label">Desde</label>
                        <input type="date" name="created_from" id="created_from" class="form-control" value="{{ $filters['created_from'] ?? '' }}">
                    </div>

                    <div class="col-md-{{ $canFilterService ? '2' : '3' }}">
                        <label for="created_to" class="form-label">Hasta</label>
                        <input type="date" name="created_to" id="created_to" class="form-control" value="{{ $filters['created_to'] ?? '' }}">
                    </div>

                    <div class="col-md-{{ $canFilterService ? '2' : '3' }}">
                        <label for="q" class="form-label">Buscar</label>
                        <input type="text" name="q" id="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="ID, motivo, nombre reemplazo">
                    </div>

                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <a href="{{ route('requests.index', ['tab' => $activeTab]) }}" class="btn btn-outline-secondary">Limpiar</a>
                        <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section id="solicitudes-panel" class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                @include('requests.partials.inbox_tabs', [
                    'tabs' => $tabs,
                    'activeTab' => $activeTab,
                    'tabCounts' => $tabCounts,
                ])

                @can('create', \App\Models\Request::class)
                    <a href="{{ route('requests.create') }}" class="btn btn-sm btn-primary">+ Nueva solicitud</a>
                @endcan
            </div>
        </div>

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
                        <tr id="request-row-{{ $request->id }}" class="request-row" data-href="{{ route('requests.show', $request) }}">
                            <td class="fw-semibold">#{{ $request->id }}</td>
                            <td>
                                <span id="status-badge-{{ $request->id }}" class="badge {{ $request->badgeClass() }}">
                                    {{ $request->status_label }}
                                </span>
                            </td>
                            <td>{{ $request->service?->name ?? 'Sin servicio' }}</td>
                            <td>{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('requests.show', $request) }}" class="btn btn-sm btn-outline-secondary js-row-action">Ver</a>
                                @if($canSend)
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary btn-send js-row-action"
                                        data-request-id="{{ $request->id }}"
                                    >
                                        Enviar
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Sin resultados para los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $requests->links() }}
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const statusClassMap = {
                BORRADOR: 'bg-primary',
                OBSERVADA: 'bg-warning text-dark',
                ENVIADA: 'bg-warning text-dark',
                EN_GESTION_PERSONAS: 'bg-warning text-dark',
                EN_RRHH: 'bg-info',
                RECHAZADA: 'bg-danger',
                EN_TRAMITACION_CONTRATO: 'bg-info',
                FINALIZADA: 'bg-success',
            };

            const statusLabelMap = {
                BORRADOR: 'Pendiente',
                OBSERVADA: 'Observada',
                ENVIADA: 'En revisión',
                EN_GESTION_PERSONAS: 'En gestión de personas',
                EN_RRHH: 'En RRHH',
                RECHAZADA: 'Rechazada',
                EN_TRAMITACION_CONTRATO: 'En contrato',
                FINALIZADA: 'Cerrada',
            };

            document.querySelectorAll('.request-row').forEach((row) => {
                row.style.cursor = 'pointer';
                row.addEventListener('click', () => {
                    window.location = row.dataset.href;
                });
            });

            document.querySelectorAll('.js-row-action').forEach((action) => {
                action.addEventListener('click', (event) => {
                    event.stopPropagation();
                });
            });

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
                            badge.textContent = statusLabelMap[result.data.status] ?? result.data.status;
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
