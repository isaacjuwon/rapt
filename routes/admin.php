<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    Volt::route('/', 'admin.dashboard')
        ->middleware(['auth'])
        ->name('dashboard');

    Volt::route('airtime', 'admin.airtime.index')
        ->name('airtime.index');

    // Data Plan Management Routes
    Volt::route('data', 'admin.data.index')
        ->name('data.index');

    Volt::route('data/create', 'admin.data.create')
        ->name('data.create');

    Volt::route('data/{dataPlan}/edit', 'admin.data.edit')
        ->name('data.edit');

    // Cable Plan Management Routes
    Volt::route('cable', 'admin.cable.index')
        ->name('cable.index');

    Volt::route('cable/create', 'admin.cable.create')
        ->name('cable.create');

    Volt::route('cable/{cablePlan}/edit', 'admin.cable.edit')
        ->name('cable.edit');

    // Education Plan Management Routes
    Volt::route('education', 'admin.education.index')
        ->name('education.index');

    Volt::route('education/create', 'admin.education.create')
        ->name('education.create');

    Volt::route('education/{educationPlan}/edit', 'admin.education.edit')
        ->name('education.edit');

    // Electricity Plan Management Routes
    Volt::route('electricity', 'admin.electricity.index')
        ->name('electricity.index');

    Volt::route('electricity/create', 'admin.electricity.create')
        ->name('electricity.create');

    Volt::route('electricity/{electricityPlan}/edit', 'admin.electricity.edit')
        ->name('electricity.edit');

    Volt::route('users', 'admin.users.index')
        ->name('users.index');

    Volt::route('brands', 'admin.brands.index')
        ->name('brands.index');

    // Permission Management Routes
    Volt::route('permissions', 'admin.permissions.index')
        ->name('permissions.index');

    Volt::route('permissions/create', 'admin.permissions.create')
        ->name('permissions.create');

    Volt::route('permissions/{permission}/edit', 'admin.permissions.edit')
        ->name('permissions.edit');

    // Role Management Routes
    Volt::route('roles', 'admin.roles.index')
        ->name('roles.index');

    Volt::route('roles/create', 'admin.roles.create')
        ->name('roles.create');

    Volt::route('roles/{role}/edit', 'admin.roles.edit')
        ->name('roles.edit');

    // Admin Settings Routes
    Volt::route('settings/general', 'admin.settings.general')
        ->name('settings.general');

    Volt::route('settings/api', 'admin.settings.api')
        ->name('settings.api');

    Volt::route('settings/shares', 'admin.settings.shares')
        ->name('settings.shares');

    Volt::route('settings/wallet', 'admin.settings.wallet')
        ->name('settings.wallet');

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

    // Bulk Mail Routes
    Volt::route('bulk-mail', 'admin.bulk-mail.index')
        ->name('bulk-mail.index');

    // Account Management Routes
    Volt::route('accounts', 'admin.account.index')
        ->name('account.index');

    // Log Management Routes
    Volt::route('log/logviewer', 'admin.log.logviewer')
        ->name('log.logviewer');
});
