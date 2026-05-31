<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PublicBody;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
#use App\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:view-profile')->only('index');
        $this->middleware('permission:edit-profile')->only('update');
        $this->middleware('permission:reset-password')->only('resetPassword');
        $this->middleware('permission:nonaktifkan-akun')->only('deactivateAccount');
    }

    public function index()
    {
        $user = User::with('publicBody')->findOrFail(Auth::id());
        return view('badanpublik.kelola_profile', compact('user'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed'
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password berhasil direset.');
    }

    public function deactivateAccount()
    {
        $user = Auth::user();
        $user->update(['is_aktif' => false]); // pastikan field active ada di tabel users
        Auth::logout();

        return redirect('/')->with('info', 'Akun Anda telah dinonaktifkan.');
    }

    public function update(UpdateProfileRequest $request, User $user): RedirectResponse
    {
        // Ambil user yang sedang login
        // Pastikan hanya bisa mengedit profil sendiri, kecuali Super Admin
        if (auth()->id() !== $user->id && !auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit profil ini.');
        }

        // Ambil semua data tervalidasi dari UpdateProfileRequest
        $validated = $request->validated();

        // Update user
        $user->update($validated);

        return redirect()->route('profile.index')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}