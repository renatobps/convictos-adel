<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->string('status')->default('pendente'); // pendente, pago, enviado, entregue, cancelado
            $table->string('payment_method')->nullable(); // mercadopago, pix, manual
            $table->string('payment_status')->nullable(); // pending, approved, rejected
            $table->string('payment_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
