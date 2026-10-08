<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\MediaImportController;
use App\Http\Controllers\Admin\MediaOutletController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PackageMediaController;
use App\Http\Controllers\Admin\SampleReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Loaded by bootstrap/app.php with: prefix "admin", name "admin.", middleware web + auth + admin.

Route::redirect('/', '/admin/dashboard');
Route::get('/dashboard', DashboardController::class)->name('dashboard');

// Packages
Route::get('/packages/archived', [PackageController::class, 'archived'])->name('packages.archived');
Route::resource('packages', PackageController::class)->except('show');
Route::patch('/packages/{package}/toggle', [PackageController::class, 'toggle'])->name('packages.toggle');
Route::patch('/packages/{package}/move/{direction}', [PackageController::class, 'move'])->name('packages.move')->whereIn('direction', ['up', 'down']);
Route::patch('/packages/{id}/restore', [PackageController::class, 'restore'])->name('packages.restore');

// Package ↔ media outlets
Route::post('/packages/{package}/media', [PackageMediaController::class, 'store'])->name('packages.media.store');
Route::patch('/packages/{package}/media/{media}', [PackageMediaController::class, 'update'])->name('packages.media.update');
Route::delete('/packages/{package}/media/{media}', [PackageMediaController::class, 'destroy'])->name('packages.media.destroy');

// Media outlets (the "Media Network" admin section)
Route::redirect('/media-network', '/admin/media');
Route::patch('/media/{media}/toggle', [MediaOutletController::class, 'toggle'])->name('media.toggle');
Route::get('/media/import', [MediaImportController::class, 'create'])->name('media.import');
Route::post('/media/import', [MediaImportController::class, 'store'])->name('media.import.store');
Route::get('/media/import/template', [MediaImportController::class, 'template'])->name('media.import.template');
Route::resource('media', MediaOutletController::class)->except('show')->parameters(['media' => 'media']);

// Sample reports
Route::get('/sample-reports', [SampleReportController::class, 'index'])->name('sample-reports.index');
Route::get('/sample-reports/create', [SampleReportController::class, 'create'])->name('sample-reports.create');
Route::post('/sample-reports', [SampleReportController::class, 'store'])->name('sample-reports.store');
Route::get('/sample-reports/{report}/download', [SampleReportController::class, 'download'])->name('sample-reports.download');
Route::delete('/sample-reports/{report}', [SampleReportController::class, 'destroy'])->name('sample-reports.destroy');

// Enquiries
Route::get('/enquiries', [EnquiryController::class, 'index'])->name('enquiries.index');
Route::get('/enquiries/export', [EnquiryController::class, 'export'])->name('enquiries.export');
Route::get('/enquiries/{enquiry}', [EnquiryController::class, 'show'])->name('enquiries.show');
Route::patch('/enquiries/{enquiry}/status', [EnquiryController::class, 'updateStatus'])->name('enquiries.status');
Route::post('/enquiries/{enquiry}/retry-emails', [EnquiryController::class, 'retryEmails'])->name('enquiries.retry');

// Website content & settings
Route::get('/content', [ContentController::class, 'edit'])->name('content.edit');
Route::get('/content/{section}', [ContentController::class, 'edit'])->name('content.section')->whereIn('section', ContentController::SECTIONS);
Route::put('/content', [ContentController::class, 'update'])->name('content.update');
Route::resource('faqs', FaqController::class)->except('show');
Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

// Account
Route::get('/account/password', [AccountController::class, 'edit'])->name('account.password');
Route::put('/account/password', [AccountController::class, 'update'])->name('account.password.update');

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
