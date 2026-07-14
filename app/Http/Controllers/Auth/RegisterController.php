<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Kategori;
use App\Models\PublicBody;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Notification;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    protected $redirectTo = '/badanpublik/beranda';

    public function __construct()
    {
        $this->middleware('guest');
    }

    // Form register
    public function showRegistrationForm()
    {
        $kategoris = Kategori::all();
        return view('auth.register', compact('kategoris'));
    }

    // AJAX ambil badan publik
    public function getPublicBodies($categoryId)
    {
        $publicBodies = PublicBody::where('kategori_id', $categoryId)
            ->where('is_registered', false)
            ->orderBy('nama_badan')
            ->get(['id', 'nama_badan']);

        return response()->json($publicBodies);
    }

    public function register()
    {
        $data = request()->all();
        $isEmail = filter_var($data['username_email'] ?? '', FILTER_VALIDATE_EMAIL);

        $validator = Validator::make($data, [
            'kategori_id' => ['required','exists:kategoris,id'],
            'public_body_id' => [
                'required',
                Rule::exists('public_bodies', 'id')
                    ->where(fn($q) =>
                        $q->where('kategori_id', $data['kategori_id'])
                          ->where('is_registered', false)
                    ),
            ],

            'nama_responden' => ['required','string','max:255'],
            'jabatan_responden' => ['required','string','max:255'],
            'nohp_responden' => ['required','string','max:20'],
            'email_responden' => ['required','email','max:255'],

            'username_email' => [
                'required',
                'string',
                'max:100',
                'unique:users,username',
                'unique:users,email',
                $isEmail ? 'email' : '',
            ],
            'password' => ['required','min:8','confirmed'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($data, $isEmail) {

            $user = User::create([
                'public_body_id' => $data['public_body_id'],
                'username' => $data['username_email'],
                'email' => $isEmail ? $data['username_email'] : null,
                'password' => Hash::make($data['password']),

                'nama_responden' => $data['nama_responden'],
                'jabatan_responden' => $data['jabatan_responden'],
                'nohp_responden' => $data['nohp_responden'],
                'email_responden' => $data['email_responden'],
            ]);

            // assign role
            $user->assignRole('Badan Publik');

            // tandai badan publik sudah register
            PublicBody::where('id', $data['public_body_id'])
                ->update(['is_registered' => true]);

            // NOTIFIKASI KE SUPER ADMIN
            $pb = PublicBody::find($data['public_body_id']);
            $superAdmins = User::role('Super Admin')->get();
            foreach ($superAdmins as $sa) {
                Notification::create([
                    'user_id' => $sa->id,
                    'title' => 'PENDAFTARAN BARU',
                    'message' => "Badan Publik {$pb->nama_badan} sudah mendaftar dan perlu diverifikasi.",
                ]);
            }
        });

        return redirect('/login')->with('success','Registrasi berhasil, silakan login.');
    }
}