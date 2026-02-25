@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@php
    $solicitudes = [
        [
            'id' => 101,
            'estado' => 'En revisión',
            'badge' => 'warning',
            'servicio' => 'Cirugía',
            'creada' => '2026-02-13 09:30',
        ],
        [
            'id' => 102,
            'estado' => 'Observada',
            'badge' => 'secondary',
            'servicio' => 'Urgencias',
            'creada' => '2026-02-13 11:10',
        ],
        [
            'id' => 103,
            'estado' => 'Aprobada',
            'badge' => 'success',
            'servicio' => 'Cardiología',
            'creada' => '2026-02-14 08:15',
        ],
    ];
@endphp

@section('content')
    <section class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h1 class="h4 mb-2">Solicitudes de Reemplazo</h1>
            <p class="text-muted mb-0">Vista inicial de tabla administrativa. Las acciones se muestran como ejemplo UI (sin persistencia).</p>
        </div>
    </section>

    <section class="card border-0 shadow-sm">
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
                    @forelse ($solicitudes as $solicitud)
                        <tr>
                            <td class="fw-semibold">#{{ $solicitud['id'] }}</td>
                            <td><span class="badge text-bg-{{ $solicitud['badge'] }}">{{ $solicitud['estado'] }}</span></td>
                            <td>{{ $solicitud['servicio'] }}</td>
                            <td>{{ $solicitud['creada'] }}</td>
                            <td class="text-end">
                                <div class="btn-group" role="group" aria-label="Acciones de solicitud">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        @click="openObservationModal({ id: {{ $solicitud['id'] }}, servicio: '{{ $solicitud['servicio'] }}' })"
                                    >
                                        Observar
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary"
                                        @click="showToast('Acción realizada para solicitud #{{ $solicitud['id'] }}')"
                                    >
                                        Simular acción
                                    </button>
                                </div>
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
