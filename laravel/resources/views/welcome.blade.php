<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-lg-5 text-center">
                    <h1 class="h3 mb-3">Sistema de Solicitudes Hospitalarias</h1>
                    <p class="text-muted mb-4">Base inicial con Laravel 11 + Bootstrap 5 + Alpine.js.</p>

                    <div class="d-flex justify-content-center gap-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-primary">Ir al dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">Iniciar sesión</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
