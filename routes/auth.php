<?php
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

    Route::get('/to-login', function (Request $request) {
        $intendedUrl = $request->session()->get('url.intended', '/');
        
        if (str_contains($intendedUrl, '/admin/')) {
            return redirect()->route('login.admin');
        }
        
        return redirect()->route('login.user');
    })->name('login');

    /*************** USER **************************/
    Route::get('register', App\Http\Controllers\Auth\User\ShowRegisterUserController::class)
        ->name('register.user');

    Route::post('register', App\Http\Controllers\Auth\User\RegisterUserController::class)
        ->name('register.user.store');

    Route::get('login', App\Http\Controllers\Auth\User\ShowLoginUserController::class)
        ->name('login.user');

    Route::post('login', App\Http\Controllers\Auth\User\LoginUserController::class)
        ->name('login.user.store');

    /*************** ADMIN **************************/
    Route::get('login-admin', App\Http\Controllers\Auth\Admin\ShowLoginAdminController::class)
        ->name('login.admin');

    Route::post('login-admin', App\Http\Controllers\Auth\Admin\LoginAdminController::class)
        ->name('login.admin.store');

    /****************** BOTH **************************/
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password/send-code', \App\Http\Controllers\Auth\SendVerificationCodeController::class)
        ->name('password.send-code');

    Route::get('forgot-password/confirm-code', \App\Http\Controllers\Auth\ShowConfirmCodeController::class)
        ->name('password.confirm-code.show');

    Route::post('forgot-password/confirm-code', \App\Http\Controllers\Auth\ConfirmCodeController::class)
        ->name('password.confirm-code');

    // Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    //     ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.confirm.store');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
