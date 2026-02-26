@extends('layouts.admin')

@section('title', 'Nueva Solicitud')
@section('page-title', 'Nueva Solicitud')
@section('breadcrumb', 'Inicio / Solicitudes / Nueva')

@section('content')
    <section class="card border-0 shadow-sm">
        <div class="card-body">
            <h1 class="h4 mb-4">Crear solicitud de reemplazo</h1>

            <form method="POST" action="{{ route('requests.store') }}" class="row g-3">
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
                    @error('motivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                    @error('fecha_inicio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                    @error('fecha_fin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                    @error('nombre_reemplazo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end mt-3">
                    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar borrador</button>
                </div>
            </form>
        </div>
    </section>
@endsection
