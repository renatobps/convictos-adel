<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membro_acesso_regionais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membro_id')->constrained('membros')->cascadeOnDelete();
            $table->foreignId('regional_id')->constrained('regionais')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['membro_id', 'regional_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membro_acesso_regionais');
    }
};
