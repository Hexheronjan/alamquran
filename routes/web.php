<?php
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;

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

use App\Http\Controllers\Admin\AdminController as AdminDashboardController;
use App\Http\Controllers\Admin\FormFieldController as AdminFormFieldController;
use App\Http\Controllers\Admin\FormGroupController as AdminFormGroupController;
use App\Http\Controllers\Admin\FormSubmissionController as AdminFormSubmissionController;
use App\Http\Controllers\Admin\SchoolController as AdminSchoolController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('register', [RegisteredUserController::class, 'store']);

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);

Route::post('logout', [LoginController::class, 'logout'])->name('logout');

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

    Route::get('/admin/form_submission', [AdminFormSubmissionController::class, 'index'])->name('admin.form_submission.index');
    Route::get('/admin/form_submission/group/{id}', [AdminFormSubmissionController::class, 'submissionsByGroup'])->name('admin.form_submission.group');
    Route::get('/admin/form_submission/detail/{submissionId}', [AdminFormSubmissionController::class, 'submissionDetail'])->name('admin.form_submission.detail_submission');
    Route::put('/admin/form_submission/update-status/{submissionId}', [AdminFormSubmissionController::class, 'updateStatus'])->name('admin.form_submission.updateStatus');

    Route::get('/admin/schools/', [AdminSchoolController::class, 'index'])->name('admin.schools.index');
    Route::get('/admin/schools/create', [AdminSchoolController::class, 'create'])->name('admin.schools.create');
    Route::post('/admin/schools/', [AdminSchoolController::class, 'store'])->name('admin.schools.store');
    Route::get('/admin/schools/{id_school}/edit', [AdminSchoolController::class, 'edit'])->name('admin.schools.edit');
    Route::put('/admin/schools/{id_school}', [AdminSchoolController::class, 'update'])->name('admin.schools.update');
    Route::delete('/admin/schools/{id_school}', [AdminSchoolController::class, 'destroy'])->name('admin.schools.destroy');

    Route::get('/admin/profile/edit', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/admin/profile/update', [AdminProfileController::class, 'update'])->name('admin.profile.update');


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
    Route::get('/user/form_submission/{submissionId}/detail', [UserFormController::class, 'detail'])->name('user.form_submission.detail_history');

    Route::get('/user/profile', [UserProfileController::class, 'edit'])->name('user.profile.edit');
    Route::put('/user/profile', [UserProfileController::class, 'update'])->name('user.profile.update');

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
