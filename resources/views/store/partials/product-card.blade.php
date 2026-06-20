<article class="product-card" data-cat="{{ $product->category }}">
  <a href="{{ route('store.show', $product) }}" class="product-media">
    <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
    @unless($product->isPurchasable())<span class="product-flag">Em breve</span>@endunless
  </a>
  <div class="product-body">
    <span class="product-cat">{{ $product->category_label }}</span>
    <h3 class="product-name"><a href="{{ route('store.show', $product) }}" style="color:inherit;text-decoration:none;">{{ $product->name }}</a></h3>
    <p class="product-desc">{{ \Illuminate\Support\Str::limit($product->description, 80) }}</p>
    <div class="product-foot">
      <div class="product-price">{{ $product->price_label }}</div>
      @if($product->isPurchasable())
        <a href="{{ route('store.show', $product) }}" class="product-btn">Comprar</a>
      @else
        <a href="{{ route('store.show', $product) }}" class="product-btn product-btn-soon">Ver detalhes</a>
      @endif
    </div>
  </div>
</article>
