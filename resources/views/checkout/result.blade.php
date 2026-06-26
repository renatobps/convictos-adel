@extends('layouts.site')

@section('title', 'Pedido — Loja Convictos')

@section('content')
<section class="result-page">
  <div class="result-inner">
    @if($type === 'success')
      <div class="result-icon success">✓</div>
      <h1 class="title">PEDIDO CONFIRMADO</h1>
      <p class="result-text">
        Obrigado! Seu pedido foi registrado com sucesso.
        @if($order) A referência do seu pedido é <strong>{{ $order->reference }}</strong>.@endif
        @if($order && $order->payment_method === 'manual')
          Nossa equipe entrará em contato pelo e-mail/WhatsApp informado para combinar o pagamento. A retirada é na {{ \App\Support\LojaRetiradaConfig::local() }} nos horários informados no checkout.
        @else
          Você receberá a confirmação por e-mail e WhatsApp (se informado). Retire seus produtos na {{ \App\Support\LojaRetiradaConfig::local() }} nos horários disponíveis.
        @endif
      </p>
    @elseif($type === 'pending')
      <div class="result-icon pending">⏳</div>
      <h1 class="title">PAGAMENTO PENDENTE</h1>
      <p class="result-text">
        Seu pagamento está sendo processado.
        @if($order) Referência: <strong>{{ $order->reference }}</strong>.@endif
        Assim que for confirmado, atualizaremos o status do seu pedido.
      </p>
    @else
      <div class="result-icon failure">✕</div>
      <h1 class="title">PAGAMENTO NÃO CONCLUÍDO</h1>
      <p class="result-text">
        Não foi possível concluir o pagamento. Você pode tentar novamente ou falar com a nossa equipe.
      </p>
    @endif

    <div class="result-actions">
      <a href="{{ route('store.index') }}" class="btn-primary">Voltar à loja</a>
      <a href="https://wa.me/{{ config('services.loja.whatsapp') }}" class="btn-outline">Falar no WhatsApp</a>
    </div>
  </div>
</section>
@endsection
