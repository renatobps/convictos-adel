@extends('layouts.site')

@section('title', 'Produtos — CONVICTOS UM 2027')
@section('description', 'Loja oficial Convictos — camisas, dry fit e camisetas da conferência jovem Convictos UM 2027.')

@section('content')
<header class="shop-hero">
  <img src="{{ asset('assets/logos/chama-cor.png') }}" alt="" class="shop-flame">
  <span class="label">Loja Oficial · 2027</span>
  <h2 class="title">VISTA A<br><span class="stroke">CONVICÇÃO</span></h2>
  <p>A linha oficial Convictos UM 2027, inspirada no futebol americano. Tipografia forte, números esportivos e o versículo João 17:21 — para uma geração que carrega a fé com identidade.</p>
</header>

<section class="shop-section">
  <div class="shop-filters">
    <button class="shop-filter active" data-filter="all">Todos</button>
    @foreach($categories as $key => $label)
      <button class="shop-filter" data-filter="{{ $key }}">{{ $label }}</button>
    @endforeach
  </div>

  @if($products->count() > 0)
  <div class="shop-grid">
    @foreach($products as $product)
      @include('store.partials.product-card', ['product' => $product])
    @endforeach
  </div>
  @else
    <p style="text-align:center;color:#8993B8;">Nenhum produto disponível no momento.</p>
  @endif

  <div class="shop-cta">
    <h3>DÚVIDAS SOBRE SEU PEDIDO?</h3>
    <p>Fale com a nossa equipe para tamanhos, valores e prazos de entrega.</p>
    <a href="https://wa.me/{{ config('services.loja.whatsapp') }}" class="btn-primary">Falar no WhatsApp</a>
  </div>
</section>

@push('scripts')
<script>
document.querySelectorAll('.shop-filter').forEach(function(btn){
  btn.addEventListener('click', function(){
    document.querySelectorAll('.shop-filter').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    var f = btn.getAttribute('data-filter');
    document.querySelectorAll('.product-card').forEach(function(card){
      var show = (f === 'all' || card.getAttribute('data-cat') === f);
      card.style.display = show ? 'flex' : 'none';
    });
  });
});
</script>
@endpush
@endsection
