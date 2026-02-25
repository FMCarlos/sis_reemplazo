@php
    $solicitudes = [
        [
            'folio' => 'SOL-2026-001',
            'paciente' => 'María Gómez',
            'servicio' => 'Cardiología',
            'fecha' => '2026-02-10',
            'estado' => 'Pendiente',
            'badge' => 'warning',
        ],
        [
            'folio' => 'SOL-2026-002',
            'paciente' => 'Luis Herrera',
            'servicio' => 'Oncología',
            'fecha' => '2026-02-11',
            'estado' => 'En revisión',
            'badge' => 'info',
        ],
        [
            'folio' => 'SOL-2026-003',
            'paciente' => 'Ana Rivas',
            'servicio' => 'Neurología',
            'fecha' => '2026-02-11',
            'estado' => 'Aprobada',
            'badge' => 'success',
        ],
    ];
@endphp

<x-app-layout>
    <section class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h1 class="h4 mb-2">Dashboard</h1>
            <p class="text-muted mb-0">Resumen inicial del flujo de solicitudes hospitalarias.</p>
        </div>
    </section>

    <section class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 pt-4 pb-0">
            <h2 class="h5 mb-1">Solicitudes recientes</h2>
            <p class="text-muted mb-0">Tabla de referencia visual para próximas integraciones AJAX.</p>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th scope="col">Folio</th>
                        <th scope="col">Paciente</th>
                        <th scope="col">Servicio</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Estado</th>
                        <th scope="col" class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($solicitudes as $solicitud)
                        <tr>
                            <td class="fw-semibold">{{ $solicitud['folio'] }}</td>
                            <td>{{ $solicitud['paciente'] }}</td>
                            <td>{{ $solicitud['servicio'] }}</td>
                            <td>{{ $solicitud['fecha'] }}</td>
                            <td><span class="badge text-bg-{{ $solicitud['badge'] }}">{{ $solicitud['estado'] }}</span></td>
                            <td class="text-end">
                                <div class="btn-group placeholder-row-action" role="group" aria-label="Acciones de fila">
                                    <button type="button" class="btn btn-sm btn-outline-primary">Ver</button>
                                    <button type="button" class="btn btn-sm btn-outline-success" @disabled($solicitud['estado'] === 'Aprobada')>
                                        Aprobar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-app-layout>
