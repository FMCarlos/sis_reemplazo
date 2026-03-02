<x-guest-layout>
    <h1 class="h4 mb-3">Iniciar sesión</h1>

    <form method="POST" action="{{ route('login') }}" class="d-grid gap-3">
        @csrf
        <div>
            <label for="email" class="form-label">Correo</label>
            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div>
            <label for="password" class="form-label">Contraseña</label>
            <input id="password" class="form-control" type="password" name="password" required>
            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="form-check">
            <input id="remember_me" class="form-check-input" type="checkbox" name="remember">
            <label class="form-check-label" for="remember_me">Recordarme</label>
        </div>

        <button class="btn btn-primary" type="submit">Entrar</button>

        <div class="d-flex justify-content-end">
            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
        </div>
    </form>
</x-guest-layout>
