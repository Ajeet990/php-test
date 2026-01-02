<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\CustomFieldController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
// Route::get('/admin', [AdminController::class, 'index']);
// Route::get('/contact-us', [ContactUsController::class, 'index']);
// Route::post('/contact-us', [ContactUsController::class, 'store']);
// Route::resource('contact-us', ContactController::class);


// =======================
// Public Routes
// =======================
Route::get('/', [ContactUsController::class, 'index'])
    ->name('contact.form');

Route::post('/contact-us', [ContactUsController::class, 'store'])
    ->name('contact.store');


// =======================
// Admin Routes
// =======================
Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/', [AdminController::class, 'index'])
            ->name('dashboard');

        // Contacts (Admin)
        Route::get('contacts/list', [ContactController::class, 'list'])->name('contacts.list');
        Route::get('contacts/merged', [ContactController::class, 'merged'])->name('contacts.merged'); // Add this
        
        // Merge routes
        Route::post('contacts/merge/initiate', [ContactController::class, 'initiateMerge'])->name('contacts.merge.initiate');
        Route::post('contacts/merge/confirm', [ContactController::class, 'confirmMerge'])->name('contacts.merge.confirm');
        
        Route::resource('contacts', ContactController::class);
        
        // Custom Fields (Admin)
        Route::get('custom-fields/list', [CustomFieldController::class, 'list'])->name('custom.list');
        Route::resource('custom-fields', CustomFieldController::class);
    });

