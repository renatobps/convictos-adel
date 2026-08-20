<?php

namespace App\Http\Controllers;

use App\Models\Igreja;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $featured = Product::query()
            ->where('active', true)
            ->where('featured', true)
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        $igrejas = Igreja::query()
            ->with('regional')
            ->orderBy('bairro')
            ->get();

        $config = DB::table('inscricao_meta_configuracoes')->first();
        $dataEventoRaw = $config?->data_evento ?? null;
        $dataEvento = $dataEventoRaw ? Carbon::parse($dataEventoRaw)->startOfDay() : null;

        $valorComCamiseta = (float) ($config?->valor_com_camiseta ?? $config?->valor_inscricao ?? 0);
        $valorSemCamiseta = (float) ($config?->valor_sem_camiseta ?? 0);

        return view('home', compact(
            'featured',
            'igrejas',
            'dataEvento',
            'valorComCamiseta',
            'valorSemCamiseta',
        ));
    }
}
