<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// --- RUTE RSVP PUBLIK ---

// Mengganti halaman welcome bawaan dengan halaman pendaftaran RSVP
Volt::route('/', 'pages.rsvp')->name('home');

// Halaman untuk konfirmasi pembayaran
Volt::route('/konfirmasi', 'pages.konfirmasi')->name('konfirmasi');

// Halaman untuk cek/download tiket
Volt::route('/tiket', 'pages.tiket')->name('tiket');


// --- RUTE ADMIN/AUTH BAWAAN (Tetap ada) ---

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});