@extends('layouts.admin')

@section('title', 'Usuarios')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h1 class="h4 mb-0">Administración de Usuarios</h1>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Nuevo usuario</a>
            </div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Servicio</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role?->label() ?? '-' }}</td>
                            <td>{{ $user->service?->name ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
