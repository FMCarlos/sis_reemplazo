@php
    use App\Enums\UserRole;

    $user = auth()->user();
    $role = $user?->role;
@endphp

<div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
    <h1 class="h5 mb-0 fw-semibold">SIS Reemplazos</h1>
    <button
        class="btn btn-outline-secondary btn-sm d-lg-none"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#sidebarMenu"
        aria-expanded="false"
        aria-controls="sidebarMenu"
    >
        Menú
    </button>
</div>

<div class="collapse d-lg-block" id="sidebarMenu">
    <nav class="nav nav-pills flex-column gap-2">
        <a class="nav-link {{ Route::is('requests.index') && !request()->filled('estado') ? 'active' : '' }}" href="{{ route('requests.index') }}">
            Solicitudes
        </a>

        @if ($role === UserRole::JEFE_SERVICIO)
            <a class="nav-link {{ Route::is('requests.create') ? 'active' : '' }}" href="{{ route('requests.create') }}">
                Nueva solicitud
            </a>
        @endif

        @if ($role === UserRole::GESTION_PERSONAS)
            <a
                class="nav-link {{ Route::is('requests.index') && request('estado') === 'EN_GESTION_PERSONAS' ? 'active' : '' }}"
                href="{{ route('requests.index', ['estado' => 'EN_GESTION_PERSONAS']) }}"
            >
                Bandeja GP
            </a>
        @endif

        @if ($role === UserRole::RRHH)
            <a
                class="nav-link {{ Route::is('requests.index') && request('estado') === 'EN_RRHH' ? 'active' : '' }}"
                href="{{ route('requests.index', ['estado' => 'EN_RRHH']) }}"
            >
                Bandeja RRHH
            </a>
        @endif

        @if ($role === UserRole::ADMIN)
            <a class="nav-link {{ Route::is('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                Usuarios
            </a>
        @endif
    </nav>
</div>
