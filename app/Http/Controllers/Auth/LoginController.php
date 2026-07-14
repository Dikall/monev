<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
    public function login(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ]);

        // Coba login dengan mencocokkan input ke kolom email terlebih dahulu,
        // jika gagal coba cocokkan ke kolom username (untuk mengakomodasi data lama/legacy).
        $credentials = ['password' => $request->password];
        $remember = $request->remember;

        $loginSuccess = Auth::attempt(array_merge(['email' => $request->email], $credentials), $remember)
            || Auth::attempt(array_merge(['username' => $request->email], $credentials), $remember);

        if ($loginSuccess) {
            $user = Auth::user();

            if ($user->hasRole('Super Admin')) {
                return redirect()->route('superadmin.dashboard');
            } elseif ($user->hasRole('Admin')) {
                return redirect()->route('admin/beranda');
            } elseif ($user->hasRole('Badan Publik')) {
                return redirect()->route('badanpublik/beranda');
            } else {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Role tidak dikenali.');
            }
        }

        return redirect()->route('login')->with('error', 'Kredensial salah.');
    }

}