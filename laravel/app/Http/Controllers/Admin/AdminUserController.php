<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with('service')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => UserRole::cases(),
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $role = UserRole::from($validated['role']);

        if ($role === UserRole::JEFE_SERVICIO && empty($validated['service_id'])) {
            return back()->withErrors(['service_id' => 'El servicio es obligatorio para Jefe de Servicio.'])->withInput();
        }

        if ($role !== UserRole::JEFE_SERVICIO) {
            $validated['service_id'] = null;
        }

        User::query()->create($validated);

        return redirect()->route('admin.users.index')->with('status', 'Usuario creado correctamente.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => UserRole::cases(),
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $role = UserRole::from($validated['role']);

        if ($role === UserRole::JEFE_SERVICIO && empty($validated['service_id'])) {
            return back()->withErrors(['service_id' => 'El servicio es obligatorio para Jefe de Servicio.'])->withInput();
        }

        if ($role !== UserRole::JEFE_SERVICIO) {
            $validated['service_id'] = null;
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (Auth::id() === $user->id) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['delete_user' => 'No puedes eliminar tu propio usuario.']);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuario eliminado correctamente.');
    }
}
