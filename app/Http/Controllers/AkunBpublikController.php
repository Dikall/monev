<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kategori;
use App\Models\PublicBody;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use App\Models\Notification;

class AkunBpublikController extends Controller
{
    public function index(Request $request)
    {
        $kategoriId = $request->get('kategori_id');
        $kategoris  = Kategori::orderBy('name')->get();

        $query = User::role('Badan Publik')->with(['publicBody']);

        if ($kategoriId) {
            $query->whereHas('publicBody', function ($q) use ($kategoriId) {
                $q->where('kategori_id', $kategoriId);
            });
        }

        $users = $query->orderBy('name')->get();

        return view('akunbpublik.index', compact('users', 'kategoris', 'kategoriId'));
    }

public function aktifkan(string $id): RedirectResponse
{
    $user = User::findOrFail($id);

    // VALIDASI WAJIB
    if (!$user->public_body_id) {
        return back()->with('error', 'User belum memiliki badan publik.');
    }

    $user->update([
        'is_aktif' => true
    ]);

    // NOTIFIKASI KE BADAN PUBLIK
    Notification::create([
        'user_id' => $user->id,
        'title' => 'AKUN TERVERIFIKASI',
        'message' => 'Akun Anda sudah terverifikasi dan sekarang Anda dapat mengakses sistem.',
    ]);

    return redirect()->route('superadmin.akunbpublik.index')
        ->with('success', 'Akun berhasil diaktifkan.');
}
 
    /**
     * Nonaktifkan akun badan publik.
     */
    public function nonaktifkan(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $user->update(['is_aktif' => false]);
 
        return redirect()->route('superadmin.akunbpublik.index')
            ->with('success', 'Akun berhasil dinonaktifkan.');
    }
 
    /**
     * Reset password ke default (misal: 'password123' atau nomor telepon).
     */
    public function resetPassword(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
 
        // Default password: 'password123' (bisa disesuaikan)
        $user->update(['password' => Hash::make('password123')]);
 
        return redirect()->route('superadmin.akunbpublik.index')
            ->with('success', 'Password berhasil direset menjadi: password123');
    }
 
    /**
     * Hapus akun badan publik.
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $user->delete();
 
        return redirect()->route('superadmin.akunbpublik.index')
            ->with('success', 'Akun badan publik berhasil dihapus.');
    }
}
