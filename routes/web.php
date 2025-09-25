<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\LlamadasController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\OnboardingLeadsController;
use App\Http\Controllers\OnboardingCallsController;
use App\Http\Controllers\LeadNotesController;
use App\Http\Controllers\OnboardingDashboardController;
use App\Http\Controllers\ContractSigningController;
use App\Http\Controllers\ContractApprovalController;
use App\Http\Controllers\UpsellController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard',[HomeController::class, 'index'] )->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios');
    Route::get('/importar_usuarios', [UsuariosController::class, 'importar_usuarios'])->name('importar_usuarios');
    Route::get('/usuarios_form/{user?}', [UsuariosController::class, 'form'])->name('usuarios.form');
    Route::post('/usuarios/guardar', [UsuariosController::class, 'guardar'])->name('usuarios.guardar');
    Route::get('/leads', [LeadsController::class, 'index'])->name('leads');
    Route::get('/importar_leads', [LeadsController::class, 'importar_leads'])->name('importar_leads');
    Route::get('/leads_form/{lead?}', [LeadsController::class, 'form'])->name('leads.form');
    Route::get('/llamadas', [LlamadasController::class, 'index'])->name('llamadas');
    Route::get('/leads/{id}/logs', [LeadsController::class, 'logs'])->name('leads.logs');
    Route::get('/contracts/{saleId}/download', [LeadsController::class, 'downloadContract'])->name('contracts.download');
    Route::post('/contracts/{saleId}/resend-email', [LeadsController::class, 'resendContractEmail'])->name('contracts.resend');
    Route::get('/leads_form/{llamada?}', [LlamadasController::class, 'form'])->name('llamadas.form');
Route::get('/llamadas/{id}/respuestas-json', [LlamadasController::class, 'respuestasJson'])->name('llamadas.respuestas.json');
Route::get('/leads/{id}/info-json', [LeadsController::class, 'infoJson'])->name('leads.info');
Route::get('sales/form/{lead}', [SalesController::class, 'form'])->name('sales.form');
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::post('store', [SalesController::class, 'store'])->name('store');
    });
        Route::post('leads/{id}/update-status', [LeadsController::class, 'updatePipelineStatus'])->name('leads.update_status');

    // Rutas de Onboarding para rol CMS
    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('dashboard', [OnboardingDashboardController::class, 'index'])->name('dashboard');
        Route::get('leads', [OnboardingLeadsController::class, 'index'])->name('leads');
        Route::get('leads/{leadId}/calls', [OnboardingCallsController::class, 'getLeadCalls'])->name('leads.calls');
        Route::get('leads/{id}/logs', [OnboardingLeadsController::class, 'logs'])->name('leads.logs');
        Route::get('contracts/{saleId}/download', [OnboardingLeadsController::class, 'downloadContract'])->name('contracts.download');
        Route::post('calls', [OnboardingCallsController::class, 'store'])->name('calls.store');
        Route::put('calls/{callId}/status', [OnboardingCallsController::class, 'updateStatus'])->name('calls.update_status');
        Route::put('calls/{callId}/reschedule', [OnboardingCallsController::class, 'reschedule'])->name('calls.reschedule');
        Route::get('calls/{callId}/logs', [OnboardingCallsController::class, 'getLogs'])->name('calls.logs');
    });

    // Rutas para gestión de notas de leads
    Route::prefix('lead-notes')->name('lead-notes.')->group(function () {
        Route::post('/', [LeadNotesController::class, 'store'])->name('store');
        Route::get('/{leadId}', [LeadNotesController::class, 'getLeadNotes'])->name('get');
        Route::delete('/{noteId}', [LeadNotesController::class, 'destroy'])->name('destroy');
    });

    // Rutas para aprobación de contratos (solo usuarios CMS)
    Route::prefix('contracts/approval')->name('contracts.approval.')->group(function () {
        Route::get('/', [ContractApprovalController::class, 'index'])->name('index');
        Route::get('/{saleId}/edit', [ContractApprovalController::class, 'edit'])->name('edit');
        Route::put('/{saleId}', [ContractApprovalController::class, 'update'])->name('update');
        Route::post('/{saleId}/approve', [ContractApprovalController::class, 'approve'])->name('approve');
        Route::post('/{saleId}/preview', [ContractApprovalController::class, 'previewAjax'])->name('preview');
    });

});

// Rutas públicas para firma de contratos (sin autenticación)
Route::prefix('contract')->name('contract.')->group(function () {
    Route::get('sign/{token}', [ContractSigningController::class, 'show'])->name('sign');
    Route::put('sign/{token}', [ContractSigningController::class, 'update'])->name('update');
    Route::get('preview/{token}', [ContractSigningController::class, 'preview'])->name('preview');
    Route::post('preview-ajax/{token}', [ContractSigningController::class, 'previewAjax'])->name('preview.ajax');
});

// Rutas para gestión de Upsells (solo admin)
Route::middleware(['auth', 'verified'])->prefix('upsell')->name('upsell.')->group(function () {
    Route::get('/', [UpsellController::class, 'index'])->name('index');
    Route::post('{sale}/pendiente', [UpsellController::class, 'markPendiente'])->name('mark.pendiente');
    Route::post('{sale}/approve', [UpsellController::class, 'approve'])->name('approve');
    Route::get('{sale}/logs', [UpsellController::class, 'getLogs'])->name('logs');
});

require __DIR__.'/auth.php';
