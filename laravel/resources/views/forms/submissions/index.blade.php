@extends('layouts.admin')

@section('title', 'Envíos de formularios')

@section('content')
    <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Envíos de formularios</h1>
            <p class="text-muted mb-0">Listado inicial de instancias enviadas para la nueva capa modular de formularios.</p>
        </div>
        <a href="{{ route('forms.index') }}" class="btn btn-outline-secondary">Ver catálogo</a>
    </section>

    <section class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Formulario</th>
                        <th>Estado</th>
                        <th>Enviado por</th>
                        <th>Fecha envío</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td class="fw-semibold">#{{ $submission->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $submission->formType?->name ?? 'Formulario no disponible' }}</div>
                                <small class="text-muted">{{ $submission->formType?->code ?? 'Sin código' }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $submission->status?->badgeClass() ?? 'bg-dark' }}">
                                    {{ $submission->status?->label() ?? 'SIN ESTADO' }}
                                </span>
                            </td>
                            <td>{{ $submission->submitter?->name ?? 'Usuario no disponible' }}</td>
                            <td>{{ $submission->submitted_at?->format('Y-m-d H:i') ?? 'Pendiente' }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                    <a href="{{ route('forms.submissions.show', $submission) }}" class="btn btn-sm btn-outline-primary">Ver detalle</a>

                                    @can('approve', $submission)
                                        <form method="POST" action="{{ route('forms.submissions.approve', $submission) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Aprobar RRHH</button>
                                        </form>
                                    @endcan

                                    @can('reject', $submission)
                                        <form method="POST" action="{{ route('forms.submissions.reject', $submission) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Rechazar RRHH</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aún no existen envíos registrados.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $submissions->links() }}
            </div>
        </div>
    </section>
@endsection
