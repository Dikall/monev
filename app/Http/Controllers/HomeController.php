<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view-dashboard', ['only' => ['index','show']]);
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('Super Admin')) {
            return redirect()->route('superadmin.dashboard');
        } elseif ($user->hasRole('Admin')) {
            return redirect()->route('admin/beranda');
        } elseif ($user->hasRole('Badan Publik')) {
            return redirect()->route('badanpublik/beranda');
        }

        auth()->logout();
        return redirect()->route('login')->with('error', 'Role tidak dikenali.');
    }
}
