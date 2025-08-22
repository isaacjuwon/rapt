<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DownloadController;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    // Settings Routes
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Services Routes
    Volt::route('services/airtime', 'services.airtime')->name('services.airtime');
    Volt::route('services/data', 'services.data')->name('services.data');
    Volt::route('services/education', 'services.education')->name('services.education');
    Volt::route('services/electricity', 'services.electricity')->name('services.electricity');
    Volt::route('services/cable', 'services.cable')->name('services.cable');

    // Loan Routes
    Volt::route('loan', 'loan.index')->name('loan.index');
    Volt::route('loans', 'loan.index')->name('loans');
    Volt::route('loan/application', 'loan.application')->name('loan.application');
    Volt::route('loan/show/{id}', 'loan.show')->name('loan.show');
    Volt::route('loan/manage-loans', 'loan.manage-loans')->name('loan.manage-loans');

    // Shares Routes
    Volt::route('shares', 'shares.index')->name('shares.index');

    // Wallet Routes
    Volt::route('wallet', 'wallet.index')->name('wallet.index');
    Volt::route('wallet/fund', 'wallet.fund')->name('wallet.fund');
    Route::get('wallet/callback', [App\Http\Controllers\WalletController::class, 'callback'])->name('wallet.callback');

    // Media Routes
    Volt::route('media/browser', 'media.media-browser')->name('media.browser');
    Volt::route('media/gallery', 'media.media-gallery')->name('media.media-gallery');
    Volt::route('media/uploader', 'media.media-uploader')->name('media.uploader');
});



require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
