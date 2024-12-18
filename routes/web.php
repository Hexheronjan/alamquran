<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
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

use App\Http\Controllers\Admin\AdminController as AdminDashboardController;
use App\Http\Controllers\Admin\FormFieldController as AdminFormFieldController;
use App\Http\Controllers\Admin\FormGroupController as AdminFormGroupController;
use App\Http\Controllers\Admin\FormSubmissionController as AdminFormSubmissionController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('register', [RegisteredUserController::class, 'store']);

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);

Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');

Route::middleware('auth:admin')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');

    // Form Groups
    Route::get('/admin/forms', [AdminFormGroupController::class, 'index'])->name('admin.form_groups.index');
    Route::get('/admin/forms/create', [AdminFormGroupController::class, 'create'])->name('admin.form_groups.create');
    Route::post('/admin/forms', [AdminFormGroupController::class, 'store'])->name('admin.form_groups.store');
    Route::get('admin/forms/{form_id}/edit', [AdminFormGroupController::class, 'edit'])->name('admin.form_groups.edit');
    Route::put('admin/forms/{form_id}', [AdminFormGroupController::class, 'update'])->name('admin.form_groups.update');
    Route::delete('admin/forms/{form_id}', [AdminFormGroupController::class, 'destroy'])->name('admin.form_groups.destroy');

    // Fields dalam Form Group
    Route::get('/admin/forms/{form_id}/fields', [AdminFormFieldController::class, 'index'])->name('admin.form_fields.index');
    Route::get('/admin/forms/{form_id}/fields/create', [AdminFormFieldController::class, 'create'])->name('admin.form_fields.create');
    Route::post('/admin/forms/{form_id}/fields', [AdminFormFieldController::class, 'store'])->name('admin.form_fields.store');
    Route::get('admin/forms/{form_id}/fields/{field_id}/edit', [AdminFormFieldController::class, 'edit'])->name('admin.form_fields.edit');
    Route::put('admin/forms/{form_id}/fields/{field_id}', [AdminFormFieldController::class, 'update'])->name('admin.form_fields.update');
    Route::delete('admin/forms/{form_id}/fields/{field_id}', [AdminFormFieldController::class, 'destroy'])->name('admin.form_fields.destroy');

    Route::get('/admin/form_submission', [AdminFormSubmissionController::class, 'verifySubmissions'])->name('admin.form_submission.index');
    Route::put('/admin/form_submission/update-status/{submissionId}', [AdminFormSubmissionController::class, 'updateStatus'])->name('admin.form_submission.updateStatus');


    // Route::get('/admin/form-fields', [AdminFormFieldController::class, 'index'])->name('admin.form_fields.index');
    // Route::get('/admin/form-fields/create', [AdminFormFieldController::class, 'create'])->name('admin.form_fields.create'); 
    // Route::post('/admin/form-fields', [AdminFormFieldController::class, 'store'])->name('admin.form_fields.store'); 
    // Route::delete('/admin/form-fields/{id_field}', [AdminFormFieldController::class, 'destroy'])->name('admin.form_fields.destroy');
  
});

Route::middleware('auth:web')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::get('/user/dashboard/', [UserDashboardController::class, 'dashboard'])->name('user.dashboard');

    Route::get('/user/form/{id_form}', [UserFormController::class, 'showForm'])->name('user.form.show');
    Route::post('/user/form/', [UserFormController::class, 'submitForm'])->name('user.form.submit'); // Menyimpan data form
    Route::get('/user/form/', [UserFormController::class, 'userHistory'])->name('user.form.history');

});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// require __DIR__.'/auth.php';
