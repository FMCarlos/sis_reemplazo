@php
    use App\Enums\UserRole;
    $formUser = $user ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Nombre</label>
        <input id="name" type="text" name="name" class="form-control" value="{{ old('name', $formUser?->name ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="email">Correo</label>
        <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $formUser?->email ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="role">Rol</label>
        <select id="role" name="role" class="form-select" required>
            <option value="">Seleccione rol</option>
            @foreach ($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $formUser?->role?->value ?? '') === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="service_id">Servicio</label>
        <select id="service_id" name="service_id" class="form-select">
            <option value="">Sin servicio</option>
            @foreach ($services as $service)
                <option value="{{ $service->id }}" @selected((string) old('service_id', $formUser?->service_id ?? '') === (string) $service->id)>{{ $service->name }}</option>
            @endforeach
        </select>
        <small class="text-muted">Obligatorio para rol Jefe de Servicio.</small>
    </div>

    <div class="col-md-6">
        <label class="form-label" for="password">{{ $isEdit ? 'Contraseña (opcional)' : 'Contraseña' }}</label>
        <input id="password" type="password" name="password" class="form-control" {{ $isEdit ? '' : 'required' }}>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger mt-3 mb-0">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
