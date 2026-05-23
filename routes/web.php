<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BadanPublikController;
use App\Http\Controllers\PedomanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\KuemonevController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\TahunController;
use App\Http\Controllers\TenggatController;
use App\Http\Controllers\PublicBodyController;
use App\Http\Controllers\IndikatorController;
use App\Http\Controllers\PertanyaanController;
use App\Http\Controllers\ImportPertanyaanController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\AkunBpublikController;
use App\Http\Controllers\KuesionerController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// -- Pedoman Monev
Route::get('/publikasi/pedmonev', [PedomanController::class, 'index'])->name('publikasi/pedmonev');

Route::get('/pedmonev', [PedomanController::class, 'index'])->name('pedmonev.index');
Route::post('/pedmonev', [PedomanController::class, 'store'])->name('pedmonev.store');
Route::delete('/pedmonev/{id}', [PedomanController::class, 'destroy'])->name('pedmonev.destroy');

Route::get('pedoman-monev', [PedomanController::class, 'publicIndex'])->name('pedoman.public');
Route::get('pedoman-monev/download/{id}', [PedomanController::class, 'download'])->name('pedoman.download');

// -- Laporan Monev
Route::get('/publikasi/lapmonev', [LaporanController::class, 'index'])->name('publikasi/lapmonev');

Route::get('/lapmonev', [LaporanController::class, 'index'])->name('lapmonev.index');
Route::post('/lapmonev', [LaporanController::class, 'store'])->name('lapmonev.store');
Route::delete('/lapmonev/{id}', [LaporanController::class, 'destroy'])->name('lapmonev.destroy');

Route::get('laporan-monev', [LaporanController::class, 'publicIndex'])->name('laporan.public');
Route::get('laporan-monev/download/{id}', [LaporanController::class, 'download'])->name('laporan.download');

// -- Kuesioner Monev
Route::get('/publikasi/kuemonev', [KuemonevController::class, 'index'])->name('publikasi/kuemonev');

Route::get('/kuemonev', [KuemonevController::class, 'index'])->name('kuemonev.index');
Route::post('/kuemonev', [KuemonevController::class, 'store'])->name('kuemonev.store');
Route::delete('/kuemonev/{id}', [KuemonevController::class, 'destroy'])->name('kuemonev.destroy');


Route::get('kuesioner-monev', [KuemonevController::class, 'publicIndex'])->name('kuesioner.public');
Route::get('kuesioner-monev/download/{id}', [KuemonevController::class, 'download'])->name('kuesioner.download');


// // Untuk request AJAX filter badan publik
// Route::get('/get-public-bodies/{categoryId}', function ($categoryId) {
//     return \App\Models\PublicBody::where('category_id', $categoryId)->get();
// });


Route::get('/register', [RegisterController::class,'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class,'register']);
Route::get('/get-public-bodies/{kategori}', [RegisterController::class,'getPublicBodies']);

Auth::routes(['register' => false]);

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class,'index'])->name('home');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile/{user}', [ProfileController::class, 'update'])->name('profile.update');

Route::resources([
    'roles' => RoleController::class,
    'users' => UserController::class,
    // 'products' => ProductController::class,
// 'permissions' => PermissionController::class,
    ]);

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');

    Route::middleware(['auth', 'role:Super Admin'])
        ->prefix('superadmin')
        ->as('superadmin.')
        ->group(function () {

            Route::get('/dashboard', [SuperAdminController::class, 'index'])
                ->name('dashboard');

            Route::resource('kategori', KategoriController::class);
            Route::resource('tahun', TahunController::class);
            Route::resource('tenggat', TenggatController::class);
            Route::resource('bpublik', PublicBodyController::class);
            Route::resource('indikator', IndikatorController::class);
            Route::resource('pertanyaan', PertanyaanController::class);
            Route::get('pertanyaan/import/template',[ImportPertanyaanController::class, 'downloadTemplate'])->name('pertanyaan.import.template');
            Route::post('pertanyaan/import',[ImportPertanyaanController::class, 'import'])->name('pertanyaan.import');
            
            Route::resource('verifikator', AdminController::class);
            Route::post('verifikator/set/{id}', [AdminController::class, 'setPublicBody'])->name('verifikator.set');
            
            Route::resource('akunbpublik', AkunBpublikController::class);
            Route::patch('akunbpublik/{id}/aktifkan',       [AkunBpublikController::class, 'aktifkan'])
                ->name('akunbpublik.aktifkan');
            
            Route::patch('akunbpublik/{id}/nonaktifkan',    [AkunBpublikController::class, 'nonaktifkan'])
                ->name('akunbpublik.nonaktifkan');
            
            Route::patch('akunbpublik/{id}/reset-password', [AkunBpublikController::class, 'resetPassword'])
                ->name('akunbpublik.resetPassword');
            
            // REKAP NILAI
            Route::get('rekap-nilai', [SuperAdminController::class, 'rekapNilai'])->name('rekap-nilai.index');
            Route::post('rekap-nilai/update-bobot', [SuperAdminController::class, 'updateBobot'])->name('rekap-nilai.update-bobot');
            Route::post('rekap-nilai/update-presentasi/{publicBodyId}', [SuperAdminController::class, 'updatePresentasi'])->name('rekap-nilai.update-presentasi');
            Route::get('rekap-nilai/export-detail/{publicBodyId}', [SuperAdminController::class, 'exportDetailExcel'])->name('rekap-nilai.export-detail');
            Route::post('rekap-nilai/reset-publish/{publicBodyId}', [SuperAdminController::class, 'resetPublish'])->name('rekap-nilai.reset-publish');

            // PENGATURAN TAMPILAN
            Route::get('settings', [AppSettingController::class, 'index'])->name('settings.index');
            Route::post('settings', [AppSettingController::class, 'update'])->name('settings.update');

        });

    
    Route::middleware(['auth', 'role:Admin|Super Admin'])->group(function () {
        Route::get('/admin/beranda', [AdminController::class, 'dashboard'])->name('admin/beranda');
        Route::get('/admin/export-excel-formatted', [AdminController::class, 'exportExcelFormatted'])->name('admin.export-excel-formatted');
        Route::get('/admin/kategori/{kategori}/list-akun', [AdminController::class, 'listAkun'])->name('admin.list-akun');
        Route::get('/admin/kategori/{kategori}/export-excel', [AdminController::class, 'exportListAkun'])->name('admin.export-list-akun');
        Route::patch('/admin/publish-nilai/{publicBody}', [AdminController::class, 'publishNilai'])->name('admin.publish-nilai');
        Route::get('/admin/verifikasi/{publicBody}', [AdminController::class, 'verifikasiPage'])->name('admin.verifikasi');
        Route::post('/admin/verifikasi/{publicBody}/simpan', [AdminController::class, 'simpanVerifikasi'])->name('admin.simpan-verifikasi');
        Route::post('/admin/verifikasi/autosave', [AdminController::class, 'autoSaveVerifikasi'])->name('admin.autosave-verifikasi');
    });


    Route::middleware(['auth', 'role:Badan Publik'])->group(function () {
        Route::get('/badanpublik/beranda', [BadanPublikController::class, 'dashboard'])->name('badanpublik/beranda');

        Route::get('badanpublik/kuesioner/isi', [KuesionerController::class, 'index'])->name('kuesioner.index');
        Route::post('badanpublik/kuesioner/simpan', [KuesionerController::class, 'store'])->name('kuesioner.store');

        Route::get('badanpublik/kuesioner', [BadanPublikController::class, 'kuesionerTab'])->name('kuesioner.tab');

        // SUBMIT KUESIONER — mengunci jawaban
        Route::post('badanpublik/kuesioner/submit', [BadanPublikController::class, 'submitKuesioner'])->name('kuesioner.submit');
        
        // HASIL PENILAIAN
        Route::get('badanpublik/kuesioner/hasil', [BadanPublikController::class, 'hasilPenilaian'])->name('kuesioner.hasil');

        Route::post('badanpublik/kuesioner/autosave', [KuesionerController::class, 'autoSave'])->name('kuesioner.autosave');
    });


});


