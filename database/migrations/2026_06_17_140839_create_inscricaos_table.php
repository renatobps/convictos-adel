<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscricoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email');
            $table->string('whatsapp')->nullable();
            $table->string('idade')->nullable();
            $table->string('igreja')->nullable();
            $table->string('cidade')->nullable();
            $table->string('status')->default('novo'); // novo, confirmado, contatado, cancelado
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscricoes');
    }
};
