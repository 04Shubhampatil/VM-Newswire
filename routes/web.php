<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Public\EnquiryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\MediaNetworkController;
use App\Http\Controllers\Public\PackageController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\SampleReportController;
use App\Http\Controllers\Public\SeoController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
Route::get('/packages/{slug}', [PackageController::class, 'show'])->name('packages.show')->where('slug', '[a-z0-9-]+');
Route::get('/packages/{package:slug}/network', [PackageController::class, 'network'])->name('packages.network');

Route::get('/media-network', MediaNetworkController::class)->name('media-network');
Route::get('/sample-reports', [SampleReportController::class, 'index'])->name('sample-reports.index');
Route::get('/reports/{package:slug}', [SampleReportController::class, 'download'])->name('reports.download');
Route::get('/reports/{package:slug}/view', [SampleReportController::class, 'view'])->name('reports.view');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

Route::post('/enquiries', [EnquiryController::class, 'store'])->middleware('throttle:enquiries')->name('enquiries.store');
Route::get('/enquiries/thank-you', [EnquiryController::class, 'thanks'])->name('enquiries.thanks');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// Admin authentication (no public registration).
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login')->name('login.store');
});
