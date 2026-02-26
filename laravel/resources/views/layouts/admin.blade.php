<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ trim($__env->yieldContent('title', 'Solicitudes')) }} | {{ config('app.name', 'SIS Reemplazos') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
<div class="admin-shell" x-data="adminUi()">
    <aside class="admin-sidebar p-3 p-lg-4">
        @include('partials.sidebar')
    </aside>

    <div class="admin-main d-flex flex-column flex-grow-1">
        <header class="admin-topbar border-bottom px-3 px-lg-4">
            <div class="container-fluid px-0 d-flex align-items-center justify-content-between">
                @auth
                    <div class="d-flex align-items-center gap-3 ms-auto">
                        <span class="text-muted small mb-0">Rol: {{ auth()->user()->role?->label() ?? '-' }}</span>
                        <span class="text-muted small mb-0">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Cerrar sesión</button>
                        </form>
                    </div>
                @endauth
            </div>
        </header>

        <main class="px-3 px-lg-4 py-4">
            <div class="container-fluid px-0 content-container">
                @yield('content')
            </div>
        </main>
    </div>

    <div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" x-text="modalTitle"></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label for="observation" class="form-label">Comentario</label>
                    <textarea id="observation" class="form-control" rows="4" x-model="modalMessage" placeholder="Ingrese observación"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" @click="confirmAction()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="feedbackToast" class="toast border-0" role="status" aria-live="polite" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <strong class="me-auto">SIS Reemplazos</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
            <div class="toast-body" x-text="toastMessage"></div>
        </div>
    </div>
</div>

@stack('scripts')
</body>
</html>
