<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="app-shell d-lg-flex">
    <aside class="app-sidebar bg-dark text-white p-3 p-lg-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h5 mb-0">{{ config('app.name', 'Hospital SIS') }}</h1>
            <button
                class="btn btn-outline-light btn-sm d-lg-none"
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
            <p class="text-white-50 small text-uppercase mb-2">Navegación</p>
            <nav class="nav nav-pills flex-column gap-2">
                <a class="nav-link active" href="{{ route('dashboard') }}">Dashboard</a>
                <span class="nav-link text-white-50 disabled">Solicitudes</span>
                <span class="nav-link text-white-50 disabled">Aprobaciones</span>
                <span class="nav-link text-white-50 disabled">Reportes</span>
            </nav>
        </div>
    </aside>

    <div class="main-wrapper flex-grow-1 d-flex flex-column">
        <header class="bg-white border-bottom px-3 px-lg-4 py-3">
            <div class="content-container mx-auto d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="h5 mb-0">Panel de administración</h2>
                    <small class="text-muted">Gestión de solicitudes hospitalarias</small>
                </div>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">Cerrar sesión</button>
                    </form>
                @endauth
            </div>
        </header>

        <main class="flex-grow-1 px-3 px-lg-4 py-4">
            <div class="content-container mx-auto">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</div>
</body>
</html>
