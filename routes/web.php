<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\OtpController;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;

use App\Http\Controllers\User\UserController as UserDashboardController;
use App\Http\Controllers\User\FormController as UserFormController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\User\DepositTransferController as UserDepositTransferController;
use App\Http\Controllers\User\UserInfaqController;
use App\Http\Controllers\User\UserSppController;
use App\Http\Controllers\User\UserDaftarUlangController;
use App\Http\Controllers\User\UserTahfidzController;
use App\Http\Controllers\User\UserAbsensiController;
use App\Http\Controllers\User\UserKegiatanController;
use App\Http\Controllers\User\UserKondisiController;
use App\Http\Controllers\User\UserKartuSantriController;
use App\Http\Controllers\User\UserSuratController;
use App\Http\Controllers\User\UserNotifikasiController;
use App\Http\Controllers\User\UserPerizinanController;
use App\Http\Controllers\User\UserPelanggaranController;
use App\Http\Controllers\User\UserSaranController;
use App\Http\Controllers\User\UserKalenderController;
use App\Http\Controllers\User\UserPrestasiController;
use App\Http\Controllers\User\UserJadwalTeleponController;

use App\Http\Controllers\Admin\AdminPelanggaranController;
use App\Http\Controllers\Admin\AdminSaranController;
use App\Http\Controllers\Admin\AdminKalenderController;
use App\Http\Controllers\Admin\AdminPrestasiController;
use App\Http\Controllers\Admin\AdminMonitoringWaliController;
use App\Http\Controllers\Admin\AdminJadwalTeleponController;


use App\Http\Controllers\Admin\AdminController as AdminDashboardController;
use App\Http\Controllers\Admin\FormFieldController as AdminFormFieldController;
use App\Http\Controllers\Admin\FormGroupController as AdminFormGroupController;
use App\Http\Controllers\Admin\FormSubmissionController as AdminFormSubmissionController;
use App\Http\Controllers\Admin\SchoolController as AdminSchoolController;
use App\Http\Controllers\Admin\AdminInvoiceSettingController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SantriController as AdminSantriController;
use App\Http\Controllers\Admin\UangSakuController as AdminUangSakuController;
use App\Http\Controllers\Admin\AngkatanController as AdminAngkatanController;
use App\Http\Controllers\Admin\PenggunaController as AdminPenggunaController;
use App\Http\Controllers\Admin\DepositTransferController as AdminDepositTransferController;
use App\Http\Controllers\Admin\OtpMonitoringController as AdminOtpMonitoringController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\AdminWhatsappController;
use App\Http\Controllers\Admin\AkunWaliController;
use App\Http\Controllers\Admin\KenaikanKelasController as AdminKenaikanKelasController;
use App\Http\Controllers\Admin\AlumniController as AdminAlumniController;
use App\Http\Controllers\Admin\RekeningKelasController as AdminRekeningKelasController;
use App\Http\Controllers\Admin\KantinProdukController as AdminKantinProdukController;
use App\Http\Controllers\Admin\KantinKasirController as AdminKantinKasirController;
use App\Http\Controllers\Admin\KantinLaporanController as AdminKantinLaporanController;
use App\Http\Controllers\Admin\SppController as AdminSppController;
use App\Http\Controllers\Admin\DaftarUlangController as AdminDaftarUlangController;
use App\Http\Controllers\Admin\KartuSantriController as AdminKartuSantriController;
use App\Http\Controllers\SantriRegistrationController;

use App\Http\Controllers\Admin\AbsensiController as AdminAbsensiController;
use App\Http\Controllers\Admin\TahfidzController as AdminTahfidzController;
use App\Http\Controllers\Admin\SuratMenyuratController as AdminSuratController;
use App\Http\Controllers\Admin\InfaqController as AdminInfaqController;
use App\Http\Controllers\Admin\BroadcastController as AdminBroadcastController;
use App\Http\Controllers\Admin\KehadiranPerizinanController as AdminKehadiranController;
use App\Http\Controllers\Admin\KegiatanSantriController as AdminKegiatanSantriController;
use App\Http\Controllers\Admin\KesehatanSantriController as AdminKesehatanSantriController;
use App\Http\Controllers\Admin\AdminBackupController;
use App\Http\Controllers\Admin\ImpersonateController;


Route::get('/', function () {
    if (auth('web')->check()) {
        return redirect()->route('user.dashboard');
    }
    if (auth('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('register', [RegisteredUserController::class, 'store']);

Route::get('/verify-otp', [OtpController::class, 'showOtpForm'])->name('otp.verify.form');
Route::post('/verify-otp', [LoginController::class, 'verifyOtp'])->name('otp.verify');

Route::get('login-user', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('login-admin', [LoginController::class, 'showLoginAdminForm'])->name('form-login-admin');
Route::post('login', [LoginController::class, 'loginAdmin'])->name('login-admin');
Route::post('/otp/send', [LoginController::class, 'sendOtp'])->name('otp.send');

Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Webhook Callback WhatsApp Gateway (OneSender)
Route::post('/api/webhook/onesender', [\App\Http\Controllers\Api\WhatsappWebhookController::class, 'handleOneSender'])->name('webhook.onesender');
Route::post('/webhook/onesender', [\App\Http\Controllers\Api\WhatsappWebhookController::class, 'handleOneSender']);

// Form Lupa Password
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');

// Kirim Link Reset Password
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

// Form Reset Password
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');

// Proses Reset Password
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

// Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
// Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
// Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
// Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');

Route::middleware('auth:admin')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Rute Dasar Admin (Bisa diakses seluruh admin aktif)
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/profile/edit', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/admin/profile/update', [AdminProfileController::class, 'update'])->name('admin.profile.update');

    // Log & Monitoring WhatsApp (Dapat diakses oleh Admin Utama, Keuangan, dan PPDB)
    Route::middleware('admin.permission:admin_management,keuangan,ppdb')->group(function () {
        Route::get('/admin/whatsapp/logs', [AdminWhatsappController::class, 'logs'])->name('admin.whatsapp.logs');
        Route::post('/admin/whatsapp/sync', [AdminWhatsappController::class, 'sync'])->name('admin.whatsapp.sync');
        Route::post('/admin/whatsapp/messages/{id}/retry', [AdminWhatsappController::class, 'retry'])->name('admin.whatsapp.retry');
        Route::post('/admin/whatsapp/messages/{id}/send-now', [AdminWhatsappController::class, 'sendNow'])->name('admin.whatsapp.send_now');
    });

    // =========================================================================
    // 1. MANAJEMEN ADMIN (Khusus Admin Utama / Super Admin)
    // =========================================================================
    Route::middleware('admin.permission:admin_management')->group(function () {
        Route::get('/admin/whatsapp', [AdminWhatsappController::class, 'index'])->name('admin.whatsapp.index');
        Route::post('/admin/whatsapp/test', [AdminWhatsappController::class, 'sendTest'])->name('admin.whatsapp.test');
        Route::post('/admin/whatsapp/settings', [AdminWhatsappController::class, 'updateSettings'])->name('admin.whatsapp.settings');
        Route::post('/admin/whatsapp/templates/{id}', [AdminWhatsappController::class, 'updateTemplate'])->name('admin.whatsapp.templates.update');

        Route::get('/admin/admins', [AdminManagementController::class, 'index'])->name('admin.admins.index');
        Route::get('/admin/admins/create', [AdminManagementController::class, 'create'])->name('admin.admins.create');
        Route::post('/admin/admins', [AdminManagementController::class, 'store'])->name('admin.admins.store');
        Route::get('/admin/admins/logs', [AdminManagementController::class, 'activityLogs'])->name('admin.admins.logs');
        Route::get('/admin/admins/{id}/edit', [AdminManagementController::class, 'edit'])->name('admin.admins.edit');
        Route::put('/admin/admins/{id}', [AdminManagementController::class, 'update'])->name('admin.admins.update');
        Route::put('/admin/admins/{id}/toggle', [AdminManagementController::class, 'toggleStatus'])->name('admin.admins.toggle');
        Route::put('/admin/admins/{id}/reset-password', [AdminManagementController::class, 'resetPassword'])->name('admin.admins.reset_password');
        Route::delete('/admin/admins/{id}', [AdminManagementController::class, 'destroy'])->name('admin.admins.destroy');
        
        // Backup Sistem & Database
        Route::get('/admin/backup', [AdminBackupController::class, 'index'])->name('admin.backup.index');
        Route::post('/admin/backup/database', [AdminBackupController::class, 'backupDatabase'])->name('admin.backup.database');
        Route::post('/admin/backup/excel', [AdminBackupController::class, 'backupExcel'])->name('admin.backup.excel');
        Route::post('/admin/backup/script', [AdminBackupController::class, 'backupScript'])->name('admin.backup.script');
        Route::post('/admin/backup/full', [AdminBackupController::class, 'backupFull'])->name('admin.backup.full');
        Route::get('/admin/backup/download/{filename}', [AdminBackupController::class, 'download'])->name('admin.backup.download');
        Route::delete('/admin/backup/delete/{filename}', [AdminBackupController::class, 'destroy'])->name('admin.backup.destroy');
        Route::post('/admin/backup/restore', [AdminBackupController::class, 'restoreDatabase'])->name('admin.backup.restore');
    });

    // =========================================================================
    // 2. MODUL PPDB & PENDAFTARAN SANTRI
    // =========================================================================
    // Tempat Sampah (Recycle Bin) Santri - Khusus Super Admin
    Route::middleware('admin.permission:admin_management')->group(function () {
        Route::get('/admin/santri/trash', [AdminSantriController::class, 'trash'])->name('admin.santri.trash');
        Route::post('/admin/santri/trash/{id}/restore', [AdminSantriController::class, 'restore'])->whereNumber('id')->name('admin.santri.restore');
        Route::post('/admin/santri/trash/restore-all', [AdminSantriController::class, 'restoreAll'])->name('admin.santri.restore_all');
        Route::delete('/admin/santri/trash/{id}/force-delete', [AdminSantriController::class, 'forceDelete'])->whereNumber('id')->name('admin.santri.force_delete');
        Route::delete('/admin/santri/trash/empty', [AdminSantriController::class, 'emptyTrash'])->name('admin.santri.empty_trash');
        Route::post('/admin/impersonate/{user}', [ImpersonateController::class, 'impersonate'])->whereNumber('user')->name('admin.impersonate');
    });

    // Input santri langsung oleh Admin PPDB. Tidak bergantung periode PPDB.
    // Diletakkan sebelum route /admin/santri/{id} agar URL /create tidak dibaca sebagai ID.
    Route::middleware('admin.permission:ppdb')->group(function () {
        Route::get('/admin/santri/create', [AdminSantriController::class, 'create'])->name('admin.santri.create');
        Route::post('/admin/santri', [AdminSantriController::class, 'store'])->name('admin.santri.store');
    });

    // Kenaikan Kelas & Kelulusan (Diletakkan sebelum /admin/santri/{id})
    Route::middleware('admin.permission:ppdb')->group(function () {
        Route::get('/admin/santri/kenaikan-kelas', [AdminKenaikanKelasController::class, 'index'])->name('admin.santri.kenaikan_kelas');
        Route::post('/admin/santri/kenaikan-kelas/process', [AdminKenaikanKelasController::class, 'process'])->name('admin.santri.kenaikan_kelas.process');
        Route::put('/admin/santri/kenaikan-kelas/{id}', [AdminKenaikanKelasController::class, 'updateIndividual'])->whereNumber('id')->name('admin.santri.kenaikan_kelas.individual');
    });

    // Read-only Santri (Dapat diakses Admin PPDB, Admin Tahfidz, dan Operator)
    Route::middleware('admin.permission:ppdb,ppdb_read,tahfidz,operator')->group(function () {
        Route::get('/admin/santri', [AdminSantriController::class, 'index'])->name('admin.santri.index');
        Route::get('/admin/santri/import/template', [AdminSantriController::class, 'downloadTemplate'])->name('admin.santri.downloadTemplate');
        Route::post('/admin/santri/import', [AdminSantriController::class, 'import'])->name('admin.santri.import');
        Route::get('/admin/santri/export/excel', [AdminSantriController::class, 'exportExcel'])->name('admin.santri.exportExcel');
        Route::get('/admin/santri/export/pdf', [AdminSantriController::class, 'exportPDF'])->name('admin.santri.exportPDF');
        Route::get('/admin/santri/kartu-massal', [AdminSantriController::class, 'cetakKartuMassal'])->name('admin.santri.cetakKartuMassal');
        Route::get('/admin/santri/poll/latest', [AdminSantriController::class, 'pollLatest'])->name('admin.santri.pollLatest');
        Route::get('/admin/santri/{id}', [AdminSantriController::class, 'show'])->whereNumber('id')->name('admin.santri.show');
        Route::get('/admin/santri/{id}/export-pdf', [AdminSantriController::class, 'exportSinglePDF'])->whereNumber('id')->name('admin.santri.exportSinglePDF');
        Route::get('/admin/santri/{id}/export-excel', [AdminSantriController::class, 'exportSingleExcel'])->whereNumber('id')->name('admin.santri.exportSingleExcel');
        Route::get('/admin/santri/{id}/kartu', [AdminSantriController::class, 'cetakKartu'])->whereNumber('id')->name('admin.santri.cetakKartu');

        // Modul Manajemen & Cetak Kartu Santri
        Route::get('/admin/kartu-santri', [AdminKartuSantriController::class, 'index'])->name('admin.kartu_santri.index');
        Route::post('/admin/kartu-santri/{santri}/foto', [AdminKartuSantriController::class, 'updateFoto'])->name('admin.kartu_santri.update_foto');
        Route::delete('/admin/kartu-santri/{santri}/foto', [AdminKartuSantriController::class, 'deleteFoto'])->name('admin.kartu_santri.delete_foto');
    });

    // Modifikasi Santri, Form Builder & Master Data (Khusus Admin PPDB)
    Route::middleware('admin.permission:ppdb')->group(function () {
        Route::get('/admin/santri/{santriId}/wali-status', [AkunWaliController::class, 'checkStatus'])->name('admin.santri.wali.status');
        Route::post('/admin/santri/{santriId}/generate-akun-wali', [AkunWaliController::class, 'generate'])->name('admin.santri.wali.generate');
        Route::post('/admin/wali/send-credentials', [AkunWaliController::class, 'sendWa'])->middleware('throttle:5,1')->name('admin.wali.send_credentials');
        // Form Groups
        Route::get('/admin/forms', [AdminFormGroupController::class, 'index'])->name('admin.form_groups.index');
        Route::get('/admin/forms/create', [AdminFormGroupController::class, 'create'])->name('admin.form_groups.create');
        Route::post('/admin/forms', [AdminFormGroupController::class, 'store'])->name('admin.form_groups.store');
        Route::get('admin/forms/{form_id}/edit', [AdminFormGroupController::class, 'edit'])->name('admin.form_groups.edit');
        Route::put('admin/forms/{form_id}', [AdminFormGroupController::class, 'update'])->name('admin.form_groups.update');
        Route::put('/admin/form_groups/{id_form}/toggle', [AdminFormGroupController::class, 'toggleStatus'])->name('admin.form_groups.toggle');
        Route::delete('admin/forms/{form_id}', [AdminFormGroupController::class, 'destroy'])->name('admin.form_groups.destroy');

        // Fields dalam Form Group
        Route::get('/admin/forms/{form_id}/fields', [AdminFormFieldController::class, 'index'])->name('admin.form_fields.index');
        Route::get('/admin/forms/{form_id}/fields/create', [AdminFormFieldController::class, 'create'])->name('admin.form_fields.create');
        Route::post('/admin/forms/{form_id}/fields', [AdminFormFieldController::class, 'store'])->name('admin.form_fields.store');
        Route::get('admin/forms/{form_id}/fields/{field_id}/edit', [AdminFormFieldController::class, 'edit'])->name('admin.form_fields.edit');
        Route::put('admin/forms/{form_id}/fields/{field_id}', [AdminFormFieldController::class, 'update'])->name('admin.form_fields.update');
        Route::delete('admin/forms/{form_id}/fields/{field_id}', [AdminFormFieldController::class, 'destroy'])->name('admin.form_fields.destroy');

        Route::get('/admin/form_submission', [AdminFormSubmissionController::class, 'index'])->name('admin.form_submission.index');
        Route::get('/admin/form_submission/group/{id}', [AdminFormSubmissionController::class, 'submissionsByGroup'])->name('admin.form_submission.group');
        Route::get('/admin/form_submission/detail/{submissionId}', [AdminFormSubmissionController::class, 'submissionDetail'])->name('admin.form_submission.detail_submission');
        Route::put('/admin/form_submission/update-status/{submissionId}', [AdminFormSubmissionController::class, 'updateStatus'])->name('admin.form_submission.updateStatus');
        Route::get('/admin/form-submission/{submissionId}/download-pdf', [AdminFormSubmissionController::class, 'downloadPDF'])
            ->name('admin.form_submission.downloadPDF');

        // Edit/Update/Delete Santri
        Route::get('/admin/santri/{id}/edit', [AdminSantriController::class, 'edit'])->whereNumber('id')->name('admin.santri.edit');
        Route::put('/admin/santri/{id}', [AdminSantriController::class, 'update'])->whereNumber('id')->name('admin.santri.update');
    });

    // Master Data (Data Madrasah & Data Angkatan)
    Route::middleware('admin.permission:master_data,ppdb')->group(function () {
        // Sekolah
        Route::get('/admin/schools/', [AdminSchoolController::class, 'index'])->name('admin.schools.index');
        Route::get('/admin/schools/create', [AdminSchoolController::class, 'create'])->name('admin.schools.create');
        Route::post('/admin/schools/', [AdminSchoolController::class, 'store'])->name('admin.schools.store');
        Route::get('/admin/schools/{id_school}/edit', [AdminSchoolController::class, 'edit'])->name('admin.schools.edit');
        Route::put('/admin/schools/{id_school}', [AdminSchoolController::class, 'update'])->name('admin.schools.update');
        Route::delete('/admin/schools/{id_school}', [AdminSchoolController::class, 'destroy'])->name('admin.schools.destroy');

        // Pengaturan Kop & Template Invoice
        Route::get('/admin/pengaturan-invoice', [AdminInvoiceSettingController::class, 'index'])->name('admin.invoice_setting.index');
        Route::put('/admin/pengaturan-invoice', [AdminInvoiceSettingController::class, 'update'])->name('admin.invoice_setting.update');
        Route::post('/admin/pengaturan-invoice/copy-school', [AdminInvoiceSettingController::class, 'copyFromSchool'])->name('admin.invoice_setting.copy_school');

        // Angkatan Management
        Route::get('/admin/angkatan', [AdminAngkatanController::class, 'index'])->name('admin.angkatan.index');
        Route::get('/admin/angkatan/create', [AdminAngkatanController::class, 'create'])->name('admin.angkatan.create');
        Route::post('/admin/angkatan', [AdminAngkatanController::class, 'store'])->name('admin.angkatan.store');
        Route::get('/admin/angkatan/{id}/edit', [AdminAngkatanController::class, 'edit'])->name('admin.angkatan.edit');
        Route::put('/admin/angkatan/{id}', [AdminAngkatanController::class, 'update'])->name('admin.angkatan.update');
        Route::delete('/admin/angkatan/{id}', [AdminAngkatanController::class, 'destroy'])->name('admin.angkatan.destroy');
    });

    Route::middleware('admin.permission:ppdb')->group(function () {
        Route::put('/admin/santri/{id}/status', [AdminSantriController::class, 'updateStatus'])->whereNumber('id')->name('admin.santri.updateStatus');
        Route::post('/admin/santri/{id}/mutasi', [AdminSantriController::class, 'updateMutasi'])->whereNumber('id')->name('admin.santri.mutasi');
        Route::post('/admin/santri/{id}/catatan-perbaikan', [AdminSantriController::class, 'storeCorrectionNote'])->whereNumber('id')->name('admin.santri.correction_note.store');
        Route::delete('/admin/santri/{id}', [AdminSantriController::class, 'destroy'])->whereNumber('id')->name('admin.santri.destroy');

        // Alumni Management
        Route::get('/admin/alumni', [AdminAlumniController::class, 'index'])->name('admin.alumni.index');
        Route::get('/admin/alumni/template', [AdminAlumniController::class, 'downloadTemplate'])->name('admin.alumni.downloadTemplate');
        Route::get('/admin/alumni/export', [AdminAlumniController::class, 'export'])->name('admin.alumni.export');
        Route::post('/admin/alumni/import', [AdminAlumniController::class, 'import'])->name('admin.alumni.import');
        Route::get('/admin/alumni/create', [AdminAlumniController::class, 'create'])->name('admin.alumni.create');
        Route::post('/admin/alumni', [AdminAlumniController::class, 'store'])->name('admin.alumni.store');
        Route::get('/admin/alumni/{id}/edit', [AdminAlumniController::class, 'edit'])->name('admin.alumni.edit');
        Route::put('/admin/alumni/{id}', [AdminAlumniController::class, 'update'])->name('admin.alumni.update');
        Route::delete('/admin/alumni/{id}', [AdminAlumniController::class, 'destroy'])->name('admin.alumni.destroy');
    });

    // Kegiatan Santri
    Route::middleware('admin.permission:kegiatan,ppdb,operator')->group(function () {
        Route::get('/admin/kegiatan', [AdminKegiatanSantriController::class, 'index'])->name('admin.kegiatan.index');
        Route::post('/admin/kegiatan', [AdminKegiatanSantriController::class, 'store'])->name('admin.kegiatan.store');
        Route::put('/admin/kegiatan/{id}', [AdminKegiatanSantriController::class, 'update'])->name('admin.kegiatan.update');
        Route::delete('/admin/kegiatan/{id}', [AdminKegiatanSantriController::class, 'destroy'])->name('admin.kegiatan.destroy');
    });

    // Catatan Kesehatan / UKS Santri (DIPISAH)
    Route::middleware('admin.permission:kesehatan,ppdb,operator')->group(function () {
        Route::get('/admin/kesehatan', [AdminKesehatanSantriController::class, 'index'])->name('admin.kesehatan.index');
        Route::post('/admin/kesehatan', [AdminKesehatanSantriController::class, 'store'])->name('admin.kesehatan.store');
        Route::put('/admin/kesehatan/{id}', [AdminKesehatanSantriController::class, 'update'])->name('admin.kesehatan.update');
        Route::patch('/admin/kesehatan/{id}/status', [AdminKesehatanSantriController::class, 'updateStatus'])->name('admin.kesehatan.update_status');
        Route::post('/admin/kesehatan/{id}/jemput', [AdminKesehatanSantriController::class, 'jemputSantri'])->name('admin.kesehatan.jemput');
        Route::delete('/admin/kesehatan/{id}', [AdminKesehatanSantriController::class, 'destroy'])->name('admin.kesehatan.destroy');
    });

    // 3.1 SPP & Administrasi Bulanan (Admin Keuangan & Admin SPP)
    Route::middleware('admin.permission:spp,keuangan')->group(function () {
        Route::get('/admin/spp', [AdminSppController::class, 'index'])->name('admin.spp.index');
        Route::post('/admin/spp/generate', [AdminSppController::class, 'generateMonthly'])->name('admin.spp.generate');
        Route::post('/admin/spp/single', [AdminSppController::class, 'storeSingleTagihan'])->name('admin.spp.store_single');
        Route::post('/admin/spp/multi-tagihan', [AdminSppController::class, 'storeMultiTagihan'])->name('admin.spp.store_multi');
        Route::delete('/admin/spp/period', [AdminSppController::class, 'destroyPeriod'])->name('admin.spp.destroy_period');
        Route::delete('/admin/spp/tunggakan/hapus-semua', [AdminSppController::class, 'destroyAllTunggakan'])->name('admin.spp.tunggakan.destroy_all');
        Route::delete('/admin/spp/{id}', [AdminSppController::class, 'destroyTagihan'])->whereNumber('id')->name('admin.spp.destroy');
        Route::put('/admin/spp/{id}', [AdminSppController::class, 'updateTagihan'])->whereNumber('id')->name('admin.spp.update');
        Route::post('/admin/spp/{id}/bayar', [AdminSppController::class, 'storePembayaran'])->whereNumber('id')->name('admin.spp.bayar');
        Route::post('/admin/spp/{id}/batalkan-lunas', [AdminSppController::class, 'batalkanLunas'])->whereNumber('id')->name('admin.spp.batalkan_lunas');
        Route::post('/admin/spp/pembayaran/{pembayaran}/terima', [AdminSppController::class, 'verifyPembayaran'])->name('admin.spp.pembayaran.verify');
        Route::post('/admin/spp/pembayaran/{pembayaran}/tolak', [AdminSppController::class, 'rejectPembayaran'])->name('admin.spp.pembayaran.reject');
        Route::post('/admin/spp/broadcast', [AdminSppController::class, 'broadcastWa'])->name('admin.spp.broadcast');
        Route::post('/admin/spp/setting', [AdminSppController::class, 'updateSetting'])->name('admin.spp.setting');
        Route::post('/admin/spp/santri/{santri}/rekening', [AdminSppController::class, 'updateSantriRekening'])->name('admin.spp.santri_rekening');
        Route::post('/admin/spp/santri/{santri}/addon', [AdminSppController::class, 'storeAddonSantri'])->name('admin.spp.santri_addon.store');
        Route::post('/admin/spp/santri/{santri}/addon-template', [AdminSppController::class, 'storeSantriAddonTemplate'])->name('admin.spp.santri_addon_template.store');
        Route::put('/admin/spp/addon-template/{template}', [AdminSppController::class, 'updateSantriAddonTemplate'])->name('admin.spp.santri_addon_template.update');
        Route::delete('/admin/spp/addon-template/{template}', [AdminSppController::class, 'destroySantriAddonTemplate'])->name('admin.spp.santri_addon_template.destroy');
        Route::post('/admin/spp/santri/{santri}/kirim-wa-tunggakan', [AdminSppController::class, 'kirimWaTunggakanWali'])->name('admin.spp.kirim_wa_tunggakan');
        Route::post('/admin/spp/santri/{santri}/lunasi-semua', [AdminSppController::class, 'lunasiSemuaTunggakanSantri'])->name('admin.spp.lunasi_semua');
        Route::delete('/admin/spp/santri/{santri}/hapus-semua-tunggakan', [AdminSppController::class, 'hapusSemuaTunggakanSantri'])->name('admin.spp.hapus_semua_tunggakan');
        Route::post('/admin/spp/tagihan/{tagihan}/addon', [AdminSppController::class, 'storeAddon'])->name('admin.spp.addon.store');
        Route::put('/admin/spp/addon/{addon}', [AdminSppController::class, 'updateAddon'])->name('admin.spp.addon.update');
        Route::delete('/admin/spp/addon/{addon}', [AdminSppController::class, 'destroyAddon'])->name('admin.spp.addon.destroy');
        Route::get('/admin/spp/tagihan/{id}/invoice', [AdminSppController::class, 'cetakInvoice'])->name('admin.spp.tagihan.invoice');
    });

    // Biaya Masuk & Daftar Ulang Santri Baru (Admin Keuangan / Bendahara)
    Route::middleware('admin.permission:daftar_ulang,keuangan')->group(function () {
        Route::get('/admin/daftar-ulang', [AdminDaftarUlangController::class, 'index'])->name('admin.daftar_ulang.index');
        Route::post('/admin/daftar-ulang/setting', [AdminDaftarUlangController::class, 'updateSetting'])->name('admin.daftar_ulang.setting');
        Route::post('/admin/daftar-ulang/santri/{santri}/atur', [AdminDaftarUlangController::class, 'updateBiayaDanRekening'])->name('admin.daftar_ulang.atur');
        Route::post('/admin/daftar-ulang/{id}/bayar-manual', [AdminDaftarUlangController::class, 'storePembayaranManual'])->name('admin.daftar_ulang.bayar_manual');
        Route::post('/admin/daftar-ulang/{id}/verifikasi', [AdminDaftarUlangController::class, 'verifikasiPembayaran'])->name('admin.daftar_ulang.verifikasi');
        Route::post('/admin/daftar-ulang/{id}/batalkan-lunas', [AdminDaftarUlangController::class, 'batalkanLunas'])->name('admin.daftar_ulang.batalkan_lunas');
        Route::post('/admin/daftar-ulang/santri/{santri}/kirim-wa-dibuka', [AdminDaftarUlangController::class, 'kirimWaDibuka'])->name('admin.daftar_ulang.kirim_wa_dibuka');
        Route::post('/admin/daftar-ulang/broadcast-wa-dibuka', [AdminDaftarUlangController::class, 'broadcastWaDibuka'])->name('admin.daftar_ulang.broadcast_wa_dibuka');
        Route::post('/admin/daftar-ulang/wa/{id}/retry', [AdminDaftarUlangController::class, 'retryWaMessage'])->name('admin.daftar_ulang.retry_wa');
        Route::post('/admin/daftar-ulang/tagihan/{id}/addon', [AdminDaftarUlangController::class, 'storeAddon'])->name('admin.daftar_ulang.addon.store');
        Route::delete('/admin/daftar-ulang/addon/{addon}', [AdminDaftarUlangController::class, 'destroyAddon'])->name('admin.daftar_ulang.addon.destroy');
        Route::get('/admin/daftar-ulang/tagihan/{id}/invoice', [AdminDaftarUlangController::class, 'cetakInvoice'])->name('admin.daftar_ulang.tagihan.invoice');
    });

    // 3.2 Kantin & Aqumart (Kasir POS, Laporan & Produk) - Admin Keuangan & Kasir
    Route::middleware('admin.permission:kantin')->group(function () {
        Route::get('/admin/kantin/kasir', [AdminKantinKasirController::class, 'index'])->name('admin.kantin.kasir.index');
        Route::get('/admin/kantin/kasir/search-santri', [AdminKantinKasirController::class, 'searchSantri'])->name('admin.kantin.kasir.search_santri');
        Route::post('/admin/kantin/kasir/checkout', [AdminKantinKasirController::class, 'checkout'])->name('admin.kantin.kasir.checkout');
        Route::get('/admin/kantin/laporan', [AdminKantinLaporanController::class, 'index'])->name('admin.kantin.laporan.index');

        Route::get('/admin/kantin/produk', [AdminKantinProdukController::class, 'index'])->name('admin.kantin.produk.index');
        Route::get('/admin/kantin/produk/generate-kode', [AdminKantinProdukController::class, 'generateKode'])->name('admin.kantin.produk.generateKode');
        Route::get('/admin/kantin/produk/create', [AdminKantinProdukController::class, 'create'])->name('admin.kantin.produk.create');
        Route::post('/admin/kantin/produk', [AdminKantinProdukController::class, 'store'])->name('admin.kantin.produk.store');
        Route::get('/admin/kantin/produk/{id}/edit', [AdminKantinProdukController::class, 'edit'])->name('admin.kantin.produk.edit');
        Route::put('/admin/kantin/produk/{id}', [AdminKantinProdukController::class, 'update'])->name('admin.kantin.produk.update');
        Route::delete('/admin/kantin/produk/{id}', [AdminKantinProdukController::class, 'destroy'])->name('admin.kantin.produk.destroy');
    });

    // 3.3 Uang Saku & Rekening Kelas (DIPISAH: Admin Uang Saku, Operator, Keuangan & Ustadz Kelas)
    Route::middleware('admin.permission:uang_saku,rekening_kelas,operator')->group(function () {
        Route::get('/admin/rekening-kelas', [AdminRekeningKelasController::class, 'index'])->name('admin.rekening_kelas.index');
        Route::post('/admin/rekening-kelas', [AdminRekeningKelasController::class, 'storeOrUpdate'])->name('admin.rekening_kelas.store');

        Route::get('/admin/uang-saku/manual', [AdminUangSakuController::class, 'createManual'])->name('admin.uang_saku.createManual');
        Route::post('/admin/uang-saku/manual', [AdminUangSakuController::class, 'storeManual'])->name('admin.uang_saku.storeManual');
        Route::get('/admin/uang-saku/settings', [AdminUangSakuController::class, 'editSettings'])->name('admin.uang_saku.settings');
        Route::put('/admin/uang-saku/settings', [AdminUangSakuController::class, 'updateSettings'])->name('admin.uang_saku.updateSettings');

        Route::get('/admin/uang-saku', [AdminUangSakuController::class, 'index'])->name('admin.uang_saku.index');
        Route::get('/admin/deposit-transfer', [AdminDepositTransferController::class, 'index'])->name('admin.deposit_transfer.index');
        Route::put('/admin/deposit-transfer/rekening', [AdminDepositTransferController::class, 'updateRekening'])->name('admin.deposit_transfer.update_rekening');
        Route::get('/admin/deposit-transfer/{deposit}/bukti', [AdminDepositTransferController::class, 'showProof'])->name('admin.deposit_transfer.proof');
        Route::put('/admin/deposit-transfer/{deposit}/setujui', [AdminDepositTransferController::class, 'approve'])->name('admin.deposit_transfer.approve');
        Route::put('/admin/deposit-transfer/{deposit}/tolak', [AdminDepositTransferController::class, 'reject'])->name('admin.deposit_transfer.reject');
        Route::delete('/admin/deposit-transfer/{deposit}', [AdminDepositTransferController::class, 'destroy'])->name('admin.deposit_transfer.destroy');
        Route::get('/admin/uang-saku/santri/{id}', [AdminUangSakuController::class, 'show'])->name('admin.uang_saku.show');
        Route::post('/admin/uang-saku/santri/{id}/deposit', [AdminUangSakuController::class, 'storeDeposit'])->name('admin.uang_saku.deposit');
        Route::post('/admin/uang-saku/santri/{id}/expense', [AdminUangSakuController::class, 'storeExpense'])->name('admin.uang_saku.expense');
        Route::delete('/admin/uang-saku/santri/{id}', [AdminUangSakuController::class, 'destroySantri'])->name('admin.uang_saku.destroySantri');
        Route::delete('/admin/uang-saku/{id}', [AdminUangSakuController::class, 'destroy'])->name('admin.uang_saku.destroy');
        Route::get('/admin/uang-saku/santri/{id}/export-pdf', [AdminUangSakuController::class, 'exportPDF'])->name('admin.uang_saku.exportPDF');
        Route::get('/admin/uang-saku/transaksi/{id}/kuitansi', [AdminUangSakuController::class, 'cetakKuitansi'])->name('admin.uang_saku.kuitansi');
    });

    // =========================================================================
    // 4. MODUL ABSENSI (Admin Kelas / Guru)
    // =========================================================================
    Route::middleware('admin.permission:absensi,ppdb')->group(function () {
        Route::get('/admin/absensi', [AdminAbsensiController::class, 'index'])->name('admin.absensi.index');
        Route::get('/admin/absensi/input', [AdminAbsensiController::class, 'inputHarian'])->name('admin.absensi.input');
        Route::post('/admin/absensi/santri', [AdminAbsensiController::class, 'storeSantri'])->name('admin.absensi.storeSantri');
        Route::post('/admin/absensi/guru', [AdminAbsensiController::class, 'storeGuru'])->name('admin.absensi.storeGuru');
        Route::get('/admin/absensi/rekap', [AdminAbsensiController::class, 'rekap'])->name('admin.absensi.rekap');
    });

    // =========================================================================
    // 5. MODUL TAHFIDZ (Admin Tahfidz)
    // =========================================================================
    Route::middleware('admin.permission:tahfidz')->group(function () {
        Route::get('/admin/tahfidz', [AdminTahfidzController::class, 'index'])->name('admin.tahfidz.index');
        Route::post('/admin/tahfidz', [AdminTahfidzController::class, 'store'])->name('admin.tahfidz.store');
        Route::delete('/admin/tahfidz/{id}', [AdminTahfidzController::class, 'destroy'])->name('admin.tahfidz.destroy');
        Route::get('/admin/tahfidz/santri/{santriId}/rekap', [AdminTahfidzController::class, 'rekapSantri'])->name('admin.tahfidz.rekap_santri');
        Route::get('/admin/tahfidz/master-surah', [AdminTahfidzController::class, 'masterSurah'])->name('admin.tahfidz.master_surah');
        Route::post('/admin/tahfidz/master-surah', [AdminTahfidzController::class, 'storeSurah'])->name('admin.tahfidz.store_surah');
        Route::match(['put', 'post'], '/admin/tahfidz/master-surah/{id}', [AdminTahfidzController::class, 'updateSurah'])->name('admin.tahfidz.update_surah');
        Route::match(['delete', 'post'], '/admin/tahfidz/master-surah/{id}', [AdminTahfidzController::class, 'destroySurah'])->name('admin.tahfidz.destroy_surah');
    });

    // =========================================================================
    // 6. MODUL SURAT MENYURAT
    // =========================================================================
    Route::middleware('admin.permission:ppdb')->group(function () {
        Route::get('/admin/surat/keluar', [AdminSuratController::class, 'indexKeluar'])->name('admin.surat.keluar.index');
        Route::get('/admin/surat/keluar/buat', [AdminSuratController::class, 'createKeluar'])->name('admin.surat.keluar.create');
        Route::post('/admin/surat/keluar', [AdminSuratController::class, 'storeKeluar'])->name('admin.surat.keluar.store');
        Route::get('/admin/surat/keluar/{id}', [AdminSuratController::class, 'showKeluar'])->name('admin.surat.keluar.show');
        Route::delete('/admin/surat/keluar/{id}', [AdminSuratController::class, 'destroyKeluar'])->name('admin.surat.keluar.destroy');

        Route::get('/admin/surat/masuk', [AdminSuratController::class, 'indexMasuk'])->name('admin.surat.masuk.index');
        Route::post('/admin/surat/masuk', [AdminSuratController::class, 'storeMasuk'])->name('admin.surat.masuk.store');
        Route::delete('/admin/surat/masuk/{id}', [AdminSuratController::class, 'destroyMasuk'])->name('admin.surat.masuk.destroy');
    });

    // =========================================================================
    // 7. MODUL INFAQ / SEDEKAH
    // =========================================================================
    Route::middleware('admin.permission:infaq,ppdb')->group(function () {
        Route::get('/admin/infaq', [AdminInfaqController::class, 'index'])->name('admin.infaq.index');
        Route::post('/admin/infaq/program', [AdminInfaqController::class, 'storeProgram'])->name('admin.infaq.storeProgram');
        Route::match(['PUT', 'POST'], '/admin/infaq/program/{id}/update', [AdminInfaqController::class, 'updateProgram'])->name('admin.infaq.updateProgram');
        Route::match(['PUT', 'PATCH'], '/admin/infaq/program/{id}/toggle', [AdminInfaqController::class, 'toggleProgram'])->name('admin.infaq.toggleProgram');
        Route::delete('/admin/infaq/program/{id}', [AdminInfaqController::class, 'destroyProgram'])->name('admin.infaq.destroyProgram');
        Route::post('/admin/infaq/transaksi', [AdminInfaqController::class, 'storeTransaksi'])->name('admin.infaq.storeTransaksi');
        Route::match(['PUT', 'PATCH'], '/admin/infaq/transaksi/{id}/konfirmasi', [AdminInfaqController::class, 'konfirmasiTransaksi'])->name('admin.infaq.konfirmasiTransaksi');
        Route::match(['PUT', 'PATCH'], '/admin/infaq/transaksi/{id}/tolak', [AdminInfaqController::class, 'tolakTransaksi'])->name('admin.infaq.tolakTransaksi');
    });

    // =========================================================================
    // 8. BROADCAST WHATSAPP
    // =========================================================================
    Route::middleware('admin.permission:ppdb,daftar_ulang,spp')->group(function () {
        Route::get('/admin/broadcast', [AdminBroadcastController::class, 'index'])->name('admin.broadcast.index');
        Route::post('/admin/broadcast/send', [AdminBroadcastController::class, 'send'])->name('admin.broadcast.send');
        Route::get('/admin/broadcast/preview', [AdminBroadcastController::class, 'previewPenerima'])->name('admin.broadcast.previewPenerima');
    });

    // =========================================================================
    // 9. KEHADIRAN WALI & PERIZINAN SANTRI (QR Code)
    // =========================================================================
    Route::middleware('admin.permission:ppdb,kesehatan,operator')->group(function () {
        Route::get('/admin/perizinan', [AdminKehadiranController::class, 'index'])->name('admin.perizinan.index');
        Route::get('/admin/perizinan/scanner', [AdminKehadiranController::class, 'scanner'])->name('admin.perizinan.scanner');
        Route::post('/admin/perizinan/scan', [AdminKehadiranController::class, 'processScan'])->name('admin.perizinan.scan');
        Route::post('/admin/perizinan/{id}/setujui', [AdminKehadiranController::class, 'setujui'])->name('admin.perizinan.setujui');
        Route::post('/admin/perizinan/{id}/tolak', [AdminKehadiranController::class, 'tolak'])->name('admin.perizinan.tolak');
        Route::patch('/admin/perizinan/{id}/kembali', [AdminKehadiranController::class, 'kembali'])->name('admin.perizinan.kembali');
    });

    // =========================================================================
    // 10. MODUL OPERATOR & MONITORING OTP
    // =========================================================================
    Route::middleware('admin.permission:otp_monitoring,pengguna,operator')->group(function () {
        // Monitoring OTP (Fallback WhatsApp Gateway)
        Route::get('/admin/monitoring-otp', [AdminOtpMonitoringController::class, 'index'])->name('admin.otp.index');
        Route::post('/admin/monitoring-otp/{user}/resend', [AdminOtpMonitoringController::class, 'resend'])->name('admin.otp.resend');
        Route::post('/admin/monitoring-otp/{user}/update-phone', [AdminOtpMonitoringController::class, 'updatePhone'])->name('admin.otp.update_phone');

        // Manajemen akun wali/pengguna hasil registrasi
        Route::get('/admin/pengguna', [AdminPenggunaController::class, 'index'])->name('admin.pengguna.index');
        Route::get('/admin/pengguna/{pengguna}/edit', [AdminPenggunaController::class, 'edit'])->name('admin.pengguna.edit');
        Route::put('/admin/pengguna/{pengguna}', [AdminPenggunaController::class, 'update'])->name('admin.pengguna.update');
    });

    // =========================================================================
    // 11. MODUL PELANGGARAN & KEDISIPLINAN SANTRI
    // =========================================================================
    Route::middleware('admin.permission:pelanggaran,ppdb,ppdb_read,operator,ustadz_kelas')->group(function () {
        Route::get('/admin/pelanggaran', [AdminPelanggaranController::class, 'index'])->name('admin.pelanggaran.index');
        Route::post('/admin/pelanggaran', [AdminPelanggaranController::class, 'store'])->name('admin.pelanggaran.store');
        Route::put('/admin/pelanggaran/{id}', [AdminPelanggaranController::class, 'update'])->name('admin.pelanggaran.update');
        Route::delete('/admin/pelanggaran/{id}', [AdminPelanggaranController::class, 'destroy'])->name('admin.pelanggaran.destroy');
    });

    // =========================================================================
    // 12. MODUL PRESTASI SANTRI
    // =========================================================================
    Route::middleware('admin.permission:prestasi,ppdb,ppdb_read,operator,ustadz_kelas,tahfidz')->group(function () {
        Route::get('/admin/prestasi', [AdminPrestasiController::class, 'index'])->name('admin.prestasi.index');
        Route::post('/admin/prestasi', [AdminPrestasiController::class, 'store'])->name('admin.prestasi.store');
        Route::put('/admin/prestasi/{id}', [AdminPrestasiController::class, 'update'])->name('admin.prestasi.update');
        Route::delete('/admin/prestasi/{id}', [AdminPrestasiController::class, 'destroy'])->name('admin.prestasi.destroy');
    });

    // =========================================================================
    // 13. MODUL KALENDER AKADEMIK ALAM QUR'AN
    // =========================================================================
    Route::middleware('admin.permission:kalender,ppdb,operator,ustadz_kelas')->group(function () {
        Route::get('/admin/kalender', [AdminKalenderController::class, 'index'])->name('admin.kalender.index');
        Route::post('/admin/kalender', [AdminKalenderController::class, 'store'])->name('admin.kalender.store');
        Route::put('/admin/kalender/{id}', [AdminKalenderController::class, 'update'])->name('admin.kalender.update');
        Route::delete('/admin/kalender/{id}', [AdminKalenderController::class, 'destroy'])->name('admin.kalender.destroy');
    });

    // =========================================================================
    // 14. MODUL KOTAK SARAN & MASUKAN PENGEMBANGAN
    // =========================================================================
    Route::middleware('admin.permission:saran,operator')->group(function () {
        Route::get('/admin/saran', [AdminSaranController::class, 'index'])->name('admin.saran.index');
        Route::match(['PUT', 'PATCH', 'POST'], '/admin/saran/{id}', [AdminSaranController::class, 'update'])->name('admin.saran.update');
        Route::match(['PUT', 'PATCH', 'POST'], '/admin/saran/{id}/status', [AdminSaranController::class, 'updateStatus'])->name('admin.saran.update-status');
        Route::get('/admin/saran/{id}/status', function () {
            return redirect()->route('admin.saran.index');
        });
        Route::get('/admin/saran/{id}', function () {
            return redirect()->route('admin.saran.index');
        });
        Route::delete('/admin/saran/{id}', [AdminSaranController::class, 'destroy'])->name('admin.saran.destroy');
    });

    // =========================================================================
    // 15. MODUL MONITORING ONLINE & AKTIVITAS WALI SANTRI
    // =========================================================================
    Route::middleware('admin.permission:monitoring_wali,operator,otp_monitoring,pengguna')->group(function () {
        Route::get('/admin/monitoring-wali', [AdminMonitoringWaliController::class, 'index'])->name('admin.monitoring.wali');
    });

    // =========================================================================
    // 16. MODUL JADWAL TELEPON SANTRI & REMINDER WHATSAPP
    // =========================================================================
    Route::middleware('admin.permission:ppdb,telepon,operator,ustadz_kelas')->group(function () {
        Route::get('/admin/jadwal-telepon', [AdminJadwalTeleponController::class, 'index'])->name('admin.telepon.index');
        Route::post('/admin/jadwal-telepon', [AdminJadwalTeleponController::class, 'store'])->name('admin.telepon.store');
        Route::post('/admin/jadwal-telepon/bulk', [AdminJadwalTeleponController::class, 'storeBulk'])->name('admin.telepon.store_bulk');
        Route::put('/admin/jadwal-telepon/{jadwal}', [AdminJadwalTeleponController::class, 'update'])->name('admin.telepon.update');
        Route::post('/admin/jadwal-telepon/{jadwal}/status', [AdminJadwalTeleponController::class, 'updateStatus'])->name('admin.telepon.update_status');
        Route::post('/admin/jadwal-telepon/{jadwal}/send-reminder', [AdminJadwalTeleponController::class, 'sendReminder'])->name('admin.telepon.send_reminder');
        Route::post('/admin/jadwal-telepon/broadcast-today', [AdminJadwalTeleponController::class, 'broadcastToday'])->name('admin.telepon.broadcast_today');
        Route::delete('/admin/jadwal-telepon/{jadwal}', [AdminJadwalTeleponController::class, 'destroy'])->name('admin.telepon.destroy');
    });

});

// Rute untuk kembali dari mode auto-login Super Admin
Route::post('/admin/impersonate/leave', [ImpersonateController::class, 'leave'])->name('admin.impersonate.leave');

Route::middleware(['auth:web', 'must.change.password', \App\Http\Middleware\UserMiddleware::class])->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::get('/user/dashboard/', [UserDashboardController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/user/change-password', [UserProfileController::class, 'showChangePassword'])->name('user.password.change');
    Route::post('/user/change-password', [UserProfileController::class, 'updateForcedPassword'])->name('user.password.update_forced');

    // Santri Registration
    Route::get('/santri/daftar', [SantriRegistrationController::class, 'showForm'])->name('santri.registration');
    Route::post('/santri/daftar', [SantriRegistrationController::class, 'store'])->name('santri.registration.store');
    Route::post('/santri/check-duplicate', [SantriRegistrationController::class, 'checkDuplicate'])->name('santri.registration.checkDuplicate');
    Route::get('/santri/status/{id}', [SantriRegistrationController::class, 'showStatus'])->name('santri.registration.status');
    Route::get('/santri/status/{id}/perbaiki', [SantriRegistrationController::class, 'editCorrection'])->name('santri.registration.correction.edit');
    Route::put('/santri/status/{id}/perbaiki', [SantriRegistrationController::class, 'updateCorrection'])->name('santri.registration.correction.update');
    Route::get('/user/uang-saku', [SantriRegistrationController::class, 'userUangSaku'])->name('user.uang_saku');
    Route::get('/user/deposit-transfer', [UserDepositTransferController::class, 'create'])->name('user.deposit_transfer.create');
    Route::post('/user/deposit-transfer', [UserDepositTransferController::class, 'store'])->name('user.deposit_transfer.store');
    Route::get('/user/spp', [UserSppController::class, 'index'])->name('user.spp.index');
    Route::post('/user/spp/konfirmasi-semua', [UserSppController::class, 'konfirmasiBayarSemua'])->name('user.spp.konfirmasi_semua');
    Route::post('/user/spp/{id}/konfirmasi', [UserSppController::class, 'konfirmasiBayar'])->name('user.spp.konfirmasi');
    Route::get('/user/spp/tagihan/{id}/invoice', [UserSppController::class, 'cetakInvoice'])->name('user.spp.tagihan.invoice');
    Route::get('/user/daftar-ulang', [UserDaftarUlangController::class, 'index'])->name('user.daftar_ulang.index');
    Route::post('/user/daftar-ulang/{id}/konfirmasi', [UserDaftarUlangController::class, 'konfirmasiBayar'])->name('user.daftar_ulang.konfirmasi');
    Route::get('/user/daftar-ulang/tagihan/{id}/invoice', [UserDaftarUlangController::class, 'cetakInvoice'])->name('user.daftar_ulang.tagihan.invoice');
    Route::get('/user/uang-saku/transaksi/{id}/kuitansi', [SantriRegistrationController::class, 'cetakKuitansiUangSaku'])->name('user.uang_saku.kuitansi');
    Route::get('/user/infaq', [UserInfaqController::class, 'index'])->name('user.infaq');
    Route::post('/user/infaq', [UserInfaqController::class, 'store'])->name('user.infaq.store');

    Route::get('/user/form/{id_form}', [UserFormController::class, 'showForm'])->name('user.form.show');
    Route::post('/user/form/', [UserFormController::class, 'submitForm'])->name('user.form.submit'); // Menyimpan data form
    Route::get('/user/form/', [UserFormController::class, 'userHistory'])->name('user.form.history');
    Route::get('/user/form_submission/{submissionId}/detail', [UserFormController::class, 'detail'])->name('user.form_submission.detail_history');
    Route::get('/user/form_submission/{submissionId}/pdf', [UserFormController::class, 'downloadPdf'])
     ->name('user.form.download_pdf');


    Route::get('/user/profile', [UserProfileController::class, 'edit'])->name('user.profile.edit');
    Route::put('/user/profile', [UserProfileController::class, 'update'])->name('user.profile.update');

    // ── TAHFIDZ ──────────────────────────────────────────────────────────────
    Route::get('/user/tahfidz', [UserTahfidzController::class, 'index'])->name('user.tahfidz');

    // ── ABSENSI ───────────────────────────────────────────────────────────────
    Route::get('/user/absensi', [UserAbsensiController::class, 'index'])->name('user.absensi');

    // ── KEGIATAN SANTRI ───────────────────────────────────────────────────────
    Route::get('/user/kegiatan', [UserKegiatanController::class, 'index'])->name('user.kegiatan');

    // ── KONDISI SANTRI ────────────────────────────────────────────────────────
    Route::get('/user/kondisi', [UserKondisiController::class, 'index'])->name('user.kondisi');

    // ── PERIZINAN & QR ────────────────────────────────────────────────────────
    Route::get('/user/perizinan', [UserPerizinanController::class, 'index'])->name('user.perizinan');
    Route::post('/user/perizinan', [UserPerizinanController::class, 'store'])->name('user.perizinan.store');

    // ── JADWAL TELEPON SANTRI ──────────────────────────────────────────────────
    Route::get('/user/jadwal-telepon', [UserJadwalTeleponController::class, 'index'])->name('user.telepon.index');

    // ── KARTU SANTRI DIGITAL ──────────────────────────────────────────────────
    Route::get('/user/kartu-santri', [UserKartuSantriController::class, 'index'])->name('user.kartu_santri');

    // ── SURAT / DOKUMEN ───────────────────────────────────────────────────────
    Route::get('/user/surat', [UserSuratController::class, 'index'])->name('user.surat');
    Route::get('/user/surat/{id}', [UserSuratController::class, 'show'])->name('user.surat.show');

    // ── NOTIFIKASI ────────────────────────────────────────────────────────────
    Route::get('/user/notifikasi', [UserNotifikasiController::class, 'index'])->name('user.notifikasi');

    // ── PELANGGARAN & KEDISIPLINAN ─────────────────────────────────────────────
    Route::get('/user/pelanggaran', [UserPelanggaranController::class, 'index'])->name('user.pelanggaran.index');

    // ── PRESTASI SANTRI ────────────────────────────────────────────────────────
    Route::get('/user/prestasi', [UserPrestasiController::class, 'index'])->name('user.prestasi.index');

    // ── KALENDER AKADEMIK ──────────────────────────────────────────────────────
    Route::get('/user/kalender', [UserKalenderController::class, 'index'])->name('user.kalender.index');

    // ── SARAN & MASUKAN ────────────────────────────────────────────────────────
    Route::get('/user/saran', [UserSaranController::class, 'index'])->name('user.saran.index');
    Route::post('/user/saran', [UserSaranController::class, 'store'])->name('user.saran.store');

}); // end auth:web group

Route::get('/dashboard', function () {

    return redirect()->route('user.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Fallback untuk menampilkan file upload /storage/... jika hosting tidak mendukung symlink
Route::get('/storage/{path}', function ($path) {
    if (str_contains($path, '..')) {
        abort(403);
    }
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        abort(404, 'File bukti tidak ditemukan di penyimpanan server.');
    }
    return response()->file($filePath);
})->where('path', '.*');

// Helper Setup khusus hosting tanpa akses Terminal / SSH
Route::get('/hosting-setup/{action}', function ($action, \Illuminate\Http\Request $request) {
    // Izinkan jika login sebagai Super Admin atau memiliki SETUP_KEY yang valid
    $isSuperAdmin = auth('admin')->check() && auth('admin')->user()->isSuperAdmin();
    $secret = $request->query('key');
    $expectedKey = env('SETUP_KEY');

    // Jika di production dan key masih default atau kosong, tolak akses via key (hanya izinkan super admin login)
    $hasValidKey = !empty($secret) && !empty($expectedKey) && hash_equals((string) $expectedKey, (string) $secret);

    if (!$isSuperAdmin && !$hasValidKey) {
        abort(403, 'Akses ditolak. Silakan login sebagai Super Admin atau konfigurasi SETUP_KEY di .env.');
    }

    try {
        switch ($action) {
            case 'storage-link':
                \Illuminate\Support\Facades\Artisan::call('storage:link');
                $output = \Illuminate\Support\Facades\Artisan::output();
                return "<pre>✅ Storage Link Berhasil:\n{$output}</pre>";

            case 'migrate':
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                $output = \Illuminate\Support\Facades\Artisan::output();
                return "<pre>✅ Migrasi Berhasil:\n{$output}</pre>";

            case 'clear':
                \Illuminate\Support\Facades\Artisan::call('optimize:clear');
                $output = \Illuminate\Support\Facades\Artisan::output();
                return "<pre>✅ Cache Berhasil Dibersihkan:\n{$output}</pre>";

            default:
                return "Pilih aksi: /hosting-setup/storage-link, /hosting-setup/migrate, atau /hosting-setup/clear.";
        }
    } catch (\Throwable $e) {
        return "<pre>❌ Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
    }
});
