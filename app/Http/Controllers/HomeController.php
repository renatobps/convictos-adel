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

        $dataEventoRaw = DB::table('inscricao_meta_configuracoes')->value('data_evento');
        $dataEvento = $dataEventoRaw ? Carbon::parse($dataEventoRaw)->startOfDay() : null;

        return view('home', compact('featured', 'igrejas', 'dataEvento'));
    }
}
