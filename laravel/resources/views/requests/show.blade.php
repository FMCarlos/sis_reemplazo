@extends('layouts.admin')

@section('title', 'Detalle solicitud')
@section('page-title', 'Detalle de solicitud')
@section('breadcrumb', 'Inicio / Solicitudes / Detalle')

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

    $status = $requestModel->status->value;
@endphp

@section('content')
    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="h4 mb-1">Solicitud #{{ $requestModel->id }}</h1>
                <p class="text-muted mb-0">Vista de detalle con trazabilidad de acciones registradas.</p>
            </div>
            <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
        </div>
    </section>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pb-0">
            <h2 class="h5 mb-0">Detalle solicitud</h2>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block">ID</small>
                    <span class="fw-semibold">#{{ $requestModel->id }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Estado</small>
                    <span class="badge text-bg-{{ $statusStyles[$status] ?? 'secondary' }}">{{ $status }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Servicio</small>
                    <span>{{ $requestModel->service?->name ?? 'Sin servicio' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Creada</small>
                    <span>{{ $requestModel->created_at?->format('Y-m-d H:i') }}</span>
                </div>
                <div class="col-md-8">
                    <small class="text-muted d-block">Motivo</small>
                    <span>{{ $requestModel->motivo ?: 'Sin motivo ingresado' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Fecha inicio</small>
                    <span>{{ $requestModel->fecha_inicio?->format('Y-m-d') ?? '—' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Fecha fin</small>
                    <span>{{ $requestModel->fecha_fin?->format('Y-m-d') ?? '—' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Nombre reemplazo</small>
                    <span>{{ $requestModel->nombre_reemplazo ?: 'No definido' }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pb-0">
            <h2 class="h5 mb-0">Acciones</h2>
        </div>
        <div class="card-body d-flex flex-wrap gap-2 align-items-center">
            @can('send', $requestModel)
                <button type="button" class="btn btn-primary" disabled>Enviar (placeholder)</button>
            @endcan

            @can('actions', $requestModel)
                @if(auth()->user()->role->value === 'GESTION_PERSONAS')
                    <button type="button" class="btn btn-outline-info" disabled>Revisar GP (placeholder)</button>
                @endif

                @if(auth()->user()->role->value === 'RRHH')
                    <button type="button" class="btn btn-outline-dark" disabled>Revisar RRHH (placeholder)</button>
                @endif
            @endcan

            @cannot('actions', $requestModel)
                <span class="text-muted">No tienes acciones disponibles para esta solicitud.</span>
            @endcannot

            @can('actions', $requestModel)
                <small class="text-muted">Sin endpoints activos de workflow en esta pantalla (solo placeholder).</small>
            @endcan
        </div>
    </section>

    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pb-0">
            <h2 class="h5 mb-0">Historial / Auditoría</h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>From -&gt; To</th>
                        <th>Comentario</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($requestModel->actions as $action)
                        <tr>
                            <td>{{ $action->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $action->user?->name ?? 'Usuario eliminado' }}</td>
                            <td>{{ $action->action }}</td>
                            <td>{{ $action->from_status }} -&gt; {{ $action->to_status }}</td>
                            <td>{{ $action->comment ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay acciones registradas.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
