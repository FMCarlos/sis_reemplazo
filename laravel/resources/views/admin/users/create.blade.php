@extends('layouts.admin')

@section('title', 'Crear usuario')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Crear usuario</h1>

            <form method="POST" action="{{ route('admin.users.store') }}" class="d-grid gap-3">
                @csrf
                @include('admin.users._form', ['isEdit' => false])
                <div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
