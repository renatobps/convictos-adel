<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')->where('status', 'pago')->update(['status' => 'em_separacao']);
        DB::table('orders')->where('status', 'enviado')->update(['status' => 'em_separacao']);
        DB::table('orders')->where('status', 'entregue')->update(['status' => 'retirado']);
    }

    public function down(): void
    {
        DB::table('orders')->where('status', 'em_separacao')->update(['status' => 'pago']);
        DB::table('orders')->where('status', 'pronto_retirada')->update(['status' => 'enviado']);
        DB::table('orders')->where('status', 'retirado')->update(['status' => 'entregue']);
    }
};
