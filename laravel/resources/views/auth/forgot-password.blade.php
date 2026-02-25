<x-guest-layout>
    <h1 class="h4 mb-3">Recuperar contraseña</h1>

    <form method="POST" action="{{ route('password.email') }}" class="d-grid gap-3">
        @csrf
        <div>
            <label for="email" class="form-label">Correo</label>
            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required>
            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <button class="btn btn-primary" type="submit">Enviar enlace</button>
    </form>
</x-guest-layout>
