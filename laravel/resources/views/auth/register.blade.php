<x-guest-layout>
    <h1 class="h4 mb-3">Crear cuenta</h1>

    <form method="POST" action="{{ route('register') }}" class="d-grid gap-3">
        @csrf
        <div>
            <label for="name" class="form-label">Nombre</label>
            <input id="name" class="form-control" type="text" name="name" value="{{ old('name') }}" required autofocus>
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div>
            <label for="email" class="form-label">Correo</label>
            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required>
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div>
            <label for="password" class="form-label">Contraseña</label>
            <input id="password" class="form-control" type="password" name="password" required>
            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required>
        </div>

        <button class="btn btn-primary" type="submit">Registrarse</button>
        <a href="{{ route('login') }}">¿Ya tienes cuenta?</a>
    </form>
</x-guest-layout>
