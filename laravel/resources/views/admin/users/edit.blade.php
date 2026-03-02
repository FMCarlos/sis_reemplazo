@extends('layouts.admin')

@section('title', 'Editar usuario')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h1 class="h4 mb-3">Editar usuario</h1>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="d-grid gap-3">
                @csrf
                @method('PUT')
                @include('admin.users._form', ['isEdit' => true, 'user' => $user])
                <div>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
