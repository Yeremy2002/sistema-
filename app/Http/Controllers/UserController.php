<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Caja;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver usuarios')->only('index');
        $this->middleware('permission:crear usuarios')->only(['create', 'store']);
        $this->middleware('permission:editar usuarios')->only(['edit', 'update']);
        $this->middleware('permission:eliminar usuarios')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::with('roles')->get();
        return view('users.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'required|array',
            'active' => 'required|boolean'
        ]);

        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'active' => $request->active
        ]);
        $usuario->syncRoles($request->roles);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $usuario)
    {
        $roles = Role::all();
        $userRoles = $usuario->roles->pluck('name')->toArray();
        return view('users.edit', compact('usuario', 'roles', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:6|confirmed',
            'roles' => 'required|array',
            'active' => 'required|boolean'
        ]);

        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->active = $request->active;
        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }
        $usuario->save();
        $usuario->syncRoles($request->roles);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $usuario)
    {
        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado exitosamente.');
    }

    public function asignarCaja(Request $request, User $usuario)
    {
        $request->validate([
            'turno' => 'required|in:matutino,nocturno',
            'saldo_inicial' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500'
        ]);

        // Verificar si ya existe una caja abierta para este turno
        $cajaAbierta = Caja::where('turno', $request->turno)
            ->where('estado', true)
            ->first();

        if ($cajaAbierta) {
            return back()->with('error', 'Ya existe una caja abierta para este turno.');
        }

        // Crear nueva caja
        $caja = new Caja();
        $caja->user_id = $usuario->id;
        $caja->turno = $request->turno;
        $caja->saldo_inicial = $request->saldo_inicial;
        $caja->saldo_actual = $request->saldo_inicial;
        $caja->observaciones = $request->observaciones;
        $caja->estado = true;
        $caja->fecha_apertura = Carbon::now();
        $caja->save();

        return redirect()->route('usuarios.edit', $usuario)
            ->with('success', 'Caja asignada correctamente al usuario.');
    }
}
