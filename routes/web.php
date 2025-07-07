<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadAttachmentController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('login');
});


require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->middleware(['role:admin|agent'])->name('dashboard');

    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('tickets/upload', [TicketController::class, 'upload'])->name('tickets.upload');
    Route::patch('tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
    Route::patch('tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
    Route::patch('tickets/{ticket}/archive', [TicketController::class, 'archive'])->name('tickets.archive');
    Route::resource('tickets', TicketController::class);

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except('show');

        Route::get('activities', ActivityController::class)->name('activities');

        Route::resource('categories', CategoryController::class);
        Route::resource('labels', LabelController::class);
    });

    Route::post('messages/{ticket}', [MessageController::class, 'store'])->name('message.store');
    Route::get('tickets/{ticket}/messages', [MessageController::class, 'getMessages'])->name('messages.get');


    Route::get('download/attachment/{mediaItem}', DownloadAttachmentController::class)->name('attachment-download');
    Route::post('/upload-temp', function (Request $request) {
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('temp-uploads', 'public');
            return response()->json(['path' => $path]);
        }
        return response()->json(['error' => 'No file uploaded'], 422);
    });
});
