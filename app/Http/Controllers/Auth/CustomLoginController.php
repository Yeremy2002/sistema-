<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CustomLoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * Override para asegurar que siempre vaya al dashboard
     * sin importar la URL intended guardada en sesión
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Limpiar cualquier URL intended de la sesión
        $request->session()->forget('url.intended');

        // Forzar redirect al dashboard
        return redirect()->intended('/dashboard');
    }

    /**
     * Get the post-login redirect path.
     *
     * Override adicional para asegurar que siempre sea dashboard
     *
     * @return string
     */
    public function redirectTo()
    {
        return '/dashboard';
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showLoginForm()
    {
        $hotel = null;
        if (Schema::hasTable('hotels')) {
            $hotel = Hotel::first();
        }

        return view('custom.login', compact('hotel'));
    }
}
