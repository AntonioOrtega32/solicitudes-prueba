<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdjuntoController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\ResolucionController;
use App\Http\Controllers\SolicitudController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('solicitudes.index'))->name('dashboard');

    // Sin esto Laravel singulariza "solicitudes" en inglés ("solicitude").
    Route::resource('solicitudes', SolicitudController::class)
        ->parameters(['solicitudes' => 'solicitud'])
        ->except(['destroy']);

    Route::post('solicitudes/{solicitud}/enviar', [SolicitudController::class, 'enviar'])->name('solicitudes.enviar');
    Route::post('solicitudes/{solicitud}/aprobar', [ResolucionController::class, 'aprobar'])->name('solicitudes.aprobar');
    Route::post('solicitudes/{solicitud}/rechazar', [ResolucionController::class, 'rechazar'])->name('solicitudes.rechazar');
    Route::post('solicitudes/{solicitud}/comentarios', [ComentarioController::class, 'store'])->name('solicitudes.comentarios.store');
    Route::get('adjuntos/{adjunto}', [AdjuntoController::class, 'descargar'])->name('adjuntos.descargar');

    // Rutas de perfil que ya trajo Breeze (déjalas como están)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';
