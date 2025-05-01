<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PasswordChangedNotification;

class PerfilController extends Controller
{
  public function edit()
  {
    $user = Auth::user();
    return view('perfil.edit', compact('user'));
  }

  public function update(Request $request)
  {
    $user = Auth::user();
    $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email,' . $user->id,
      'current_password' => 'nullable|required_with:password|string',
      'password' => 'nullable|string|min:6|confirmed',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;

    if ($request->filled('password')) {
      if (!Hash::check($request->current_password, $user->password)) {
        return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
      }
      $user->password = Hash::make($request->password);
      $user->save();
      $user->notify(new PasswordChangedNotification());
      return back()->with('success', 'Perfil actualizado y contraseña cambiada.');
    }

    $user->save();
    return back()->with('success', 'Perfil actualizado correctamente.');
  }
}
