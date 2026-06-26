<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EvolutionWebhookController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IngressoController;
use App\Http\Controllers\InscricaoController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/inscricao', [InscricaoController::class, 'store'])->name('inscricao.store');

// Ingresso digital (QR Code)
Route::get('/ingresso/{inscricao:codigo}', [IngressoController::class, 'show'])->name('ingresso.show');
Route::get('/ingresso/{inscricao:codigo}/qr', [IngressoController::class, 'qr'])->name('ingresso.qr');
Route::get('/ingresso/{inscricao:codigo}/comprovante.pdf', [IngressoController::class, 'pdf'])->name('ingresso.pdf');

// Loja
Route::get('/loja', [StoreController::class, 'index'])->name('store.index');
Route::get('/loja/{product:slug}', [StoreController::class, 'show'])->name('store.show');

// Carrinho
Route::get('/carrinho', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrinho', [CartController::class, 'add'])->name('cart.add');
Route::patch('/carrinho/{rowId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/carrinho/{rowId}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/sucesso', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/falha', [CheckoutController::class, 'failure'])->name('checkout.failure');
Route::get('/checkout/pendente', [CheckoutController::class, 'pending'])->name('checkout.pending');

// Webhook MercadoPago
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->name('webhooks.mercadopago');

// Webhook Evolution API (enquetes / mensagens recebidas)
Route::post('/webhook', [EvolutionWebhookController::class, 'handle'])
    ->name('webhooks.evolution');
Route::post('/webhook/{event}', [EvolutionWebhookController::class, 'handle'])
    ->where('event', '.*')
    ->name('webhooks.evolution.event');
