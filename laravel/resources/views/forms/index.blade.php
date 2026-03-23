@extends('layouts.admin')

@section('title', 'Catálogo de formularios')

@section('content')
    <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Catálogo de formularios</h1>
            <p class="text-muted mb-0">Explora los tipos de formularios disponibles dentro de la nueva plataforma institucional.</p>
        </div>
        <a href="{{ route('forms.submissions.index') }}" class="btn btn-outline-primary">Ver envíos</a>
    </section>

    <section class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-4">
                @forelse ($formTypes as $formType)
                    <div class="col-md-6 col-xl-4">
                        <article class="card h-100 border-0 shadow-sm bg-light">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                    <div>
                                        <h2 class="h5 mb-1">{{ $formType->name }}</h2>
                                        <p class="text-muted small mb-0">Código: {{ $formType->code }}</p>
                                    </div>
                                    <span class="badge {{ $formType->active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $formType->active ? 'Disponible' : 'Próximamente' }}
                                    </span>
                                </div>

                                <p class="text-muted flex-grow-1 mb-4">{{ $formType->description ?: 'Sin descripción disponible todavía.' }}</p>

                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                                    <small class="text-muted">{{ $formType->submissions_count }} envío(s) registrados</small>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>Próximamente</button>
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="border rounded-3 p-4 text-center text-muted bg-light">
                            Aún no existen tipos de formularios configurados en la plataforma.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
