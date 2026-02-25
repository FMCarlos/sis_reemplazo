<x-guest-layout>
    <h1 class="h4 mb-3">Restablecer contraseña</h1>

    <form method="POST" action="{{ route('password.store') }}" class="d-grid gap-3">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="form-label">Correo</label>
            <input id="email" class="form-control" type="email" name="email" value="{{ old('email', $request->email) }}" required>
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

        <button class="btn btn-primary" type="submit">Restablecer</button>
    </form>
</x-guest-layout>
