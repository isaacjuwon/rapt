<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    Volt::route('/', 'admin.dashboard')
        ->middleware(['auth'])
        ->name('dashboard');

    Volt::route('airtime', 'admin.airtime.index')
        ->name('airtime.index');

    Volt::route('users', 'admin.users.index')
        ->name('users.index');

    Volt::route('brands', 'admin.brands.index')
        ->name('brands.index');

    // Admin Settings Routes
    Volt::route('settings/general', 'admin.settings.general')
        ->name('settings.general');

    Volt::route('settings/api', 'admin.settings.api')
        ->name('settings.api');

    Volt::route('settings/shares', 'admin.settings.shares')
        ->name('settings.shares');

    Volt::route('settings/loans', 'admin.settings.loans')
        ->name('settings.loans');

    Volt::route('settings/security', 'admin.settings.security')
        ->name('settings.security');

    Volt::route('settings/mail', 'admin.settings.mail')
        ->name('settings.mail');

    Volt::route('settings/notifications', 'admin.settings.notifications')
        ->name('settings.notifications');

    Volt::route('settings/appearance', 'admin.settings.appearance')
        ->name('settings.appearance');

    // Share Management Routes
    Volt::route('shares', 'admin.shares.index')
        ->name('shares.index');

    // Transaction Management Routes
    Volt::route('transactions', 'admin.transactions.index')
        ->name('transactions.index');

    // Payment Management Routes
    Volt::route('payments', 'admin.payments.index')
        ->name('payments.index');

    // Loan Management Routes
    Volt::route('loans', 'admin.loans.index')
        ->name('loans.index');

    // Log Management Routes
    Volt::route('log/logviewer', 'admin.log.logviewer')
        ->name('log.logviewer');
});
