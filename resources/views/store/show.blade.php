@extends('layouts.site')

@section('title', $product->name . ' — Loja Convictos')
@section('description', \Illuminate\Support\Str::limit($product->description, 150))

@section('content')
<section class="product-detail">
  <div class="product-detail-grid">
    <div class="product-detail-media">
      <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
    </div>
    <div class="product-detail-info">
      <a href="{{ route('store.index') }}" class="product-back">← Voltar à loja</a>
      <span class="product-cat">{{ $product->category_label }}</span>
      <h1 class="product-detail-name">{{ $product->name }}</h1>
      <div class="product-detail-price">R$ {{ number_format($product->price, 2, ',', '.') }}</div>
      <p class="product-detail-desc">{{ $product->description }}</p>

      <form method="POST" action="{{ route('cart.add') }}" class="product-form">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">

        @if(!empty($product->sizes))
        <div class="field">
          <label>Tamanho</label>
          <div class="size-options">
            @foreach($product->sizes as $i => $size)
              <label class="size-chip">
                <input type="radio" name="size" value="{{ $size }}" {{ $i === 0 ? 'checked' : '' }}>
                <span>{{ $size }}</span>
              </label>
            @endforeach
          </div>
        </div>
        @endif

        <div class="field">
          <label>Quantidade</label>
          <input type="number" name="quantity" value="1" min="1" max="50" class="qty-input">
        </div>

        <button type="submit" class="btn-primary" style="width:100%;">Adicionar ao carrinho</button>
      </form>

      <a href="https://wa.me/{{ config('services.loja.whatsapp') }}?text={{ urlencode('Olá! Tenho interesse no produto: ' . $product->name) }}" class="product-whats">Tirar dúvida no WhatsApp</a>
    </div>
  </div>

  @if($related->count() > 0)
  <div class="related">
    <h3 class="related-title">Você também pode gostar</h3>
    <div class="shop-grid">
      @foreach($related as $item)
        @include('store.partials.product-card', ['product' => $item])
      @endforeach
    </div>
  </div>
  @endif
</section>
@endsection
