<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('igrejas', function (Blueprint $table) {
            $table->id();
            $table->string('bairro');
            $table->string('dirigente')->nullable();
            $table->foreignId('dirigente_membro_id')->nullable()->constrained('membros')->nullOnDelete();
            $table->foreignId('regional_id')->constrained('regionais')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('igrejas');
    }
};
