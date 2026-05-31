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
        $status     = $request->get('status'); // 'aktif', 'nonaktif', atau null
        $kategoris  = Kategori::orderBy('name')->get();

        $query = User::role('Badan Publik')->with(['publicBody']);

        if ($kategoriId) {
            $query->whereHas('publicBody', function ($q) use ($kategoriId) {
                $q->where('kategori_id', $kategoriId);
            });
        }

        if ($status === 'aktif') {
            $query->where('is_aktif', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_aktif', false);
        }

        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $users = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return view('superadmin.kelola_badanpublik', compact('users', 'kategoris', 'kategoriId', 'status'));
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
     * Reset password badan publik dengan password baru yang diinput admin.
     */
    public function resetPassword(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ], [
            'password.required'              => 'Password baru wajib diisi.',
            'password.min'                   => 'Password minimal 8 karakter.',
            'password.confirmed'             => 'Konfirmasi password tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
        ]);

        $user = User::findOrFail($id);
        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('superadmin.akunbpublik.index')
            ->with('success', 'Password akun ' . ($user->publicBody->nama_badan ?? $user->name) . ' berhasil diperbarui.');
    }
 
    /**
     * Hapus akun badan publik.
     * Data PublicBody TIDAK ikut dihapus agar:
     * 1. Badan publik bisa mendaftar kembali (is_registered = false)
     * 2. Data rekap jawaban tetap dapat diidentifikasi (public_body_id tetap ada)
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Reset status registrasi agar badan publik bisa daftar ulang
        if ($user->public_body_id) {
            \App\Models\PublicBody::where('id', $user->public_body_id)
                ->update(['is_registered' => false]);
        }

        $namaBadan = optional($user->publicBody)->nama_badan ?? $user->name;

        $user->delete();

        return redirect()->route('superadmin.akunbpublik.index')
            ->with('success', 'Akun ' . $namaBadan . ' berhasil dihapus. Badan publik dapat mendaftar kembali.');
    }
}
