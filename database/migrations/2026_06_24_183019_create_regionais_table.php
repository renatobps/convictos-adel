<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regionais', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('pastor_responsavel');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regionais');
    }
};
