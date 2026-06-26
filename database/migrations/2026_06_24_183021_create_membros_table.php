<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membros', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->nullable()->unique();
            $table->string('senha')->nullable();
            $table->string('foto')->nullable();
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete();
            $table->string('telefone', 40)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membros');
    }
};
