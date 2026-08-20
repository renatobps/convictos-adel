@extends('layouts.site')

@section('title', 'Pagamento — Loja Convictos')

@section('content')
<section class="checkout-page">
  <div class="checkout-inner">
    <span class="label">Checkout transparente</span>
    <h1 class="title" style="margin-bottom:16px;">PAGAMENTO</h1>
    <p class="checkout-note" style="margin-bottom:32px;">
      Pedido <strong>{{ $order->reference }}</strong> — pague sem sair do site.
      @if(!empty($sandbox))
        <br><span style="color:#E8B86D;">Modo teste: o Mercado Pago usa e-mail @testuser.com automaticamente.</span>
      @endif
    </p>

    <div id="checkout-pay-error" class="flash flash-error" style="display:none;position:static;margin:0 0 24px;"></div>

    <div class="checkout-grid">
      <div class="checkout-form">
        <div id="paymentBrick_container" class="mp-brick-wrap"></div>
        <div id="statusScreenBrick_container" class="mp-brick-wrap" style="display:none;"></div>
        <p id="checkout-pay-hint" class="checkout-note">Cartão, Pix e outros métodos processados pelo Mercado Pago, direto nesta página.</p>
      </div>

      <aside class="checkout-summary">
        <div class="checkout-pickup">
          <span class="checkout-pickup-label">Retirada</span>
          <h3>{{ $retirada['local'] }}</h3>
          <p class="checkout-pickup-text">{{ $retirada['instrucoes'] }}</p>
          @if(count($retirada['horarios']))
            <ul class="checkout-pickup-hours">
              @foreach($retirada['horarios'] as $horario)
                <li>{{ \App\Support\LojaRetiradaConfig::formatarHorario($horario) }}</li>
              @endforeach
            </ul>
          @endif
        </div>

        <h3>Resumo do pedido</h3>
        @foreach($order->items as $item)
          <div class="summary-row">
            <span>{{ $item->quantity }}× {{ $item->product_name }}@if($item->size) ({{ $item->size }})@endif</span>
            <span>R$ {{ number_format($item->subtotal, 2, ',', '.') }}</span>
          </div>
        @endforeach
        <div class="summary-total">
          <span>Total</span>
          <span>R$ {{ number_format($order->total, 2, ',', '.') }}</span>
        </div>
      </aside>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
(function () {
  const publicKey = @json($publicKey);
  const amount = {{ $amount }};
  const payerEmail = @json($payerEmail);
  const processUrl = @json(route('checkout.pay', $order));
  const csrf = @json(csrf_token());
  const errorBox = document.getElementById('checkout-pay-error');
  const payBox = document.getElementById('paymentBrick_container');
  const statusBox = document.getElementById('statusScreenBrick_container');
  const hint = document.getElementById('checkout-pay-hint');

  function showError(msg) {
    if (!errorBox) return;
    errorBox.textContent = msg || 'Não foi possível processar o pagamento.';
    errorBox.style.display = 'block';
  }

  const mp = new MercadoPago(publicKey, { locale: 'pt-BR' });
  const bricks = mp.bricks();

  function showPixFallback(pix, redirectUrl) {
    if (window.paymentBrickController) {
      try { window.paymentBrickController.unmount(); } catch (e) {}
    }
    if (payBox) payBox.style.display = 'none';
    if (statusBox) {
      statusBox.style.display = 'block';
      let html = '<div style="color:#fff;text-align:center;padding:16px;">';
      html += '<p style="margin:0 0 12px;font-family:Oswald,sans-serif;letter-spacing:1px;">PIX GERADO</p>';
      if (pix.qr_code_base64) {
        html += '<img alt="QR Code Pix" style="max-width:260px;width:100%;background:#fff;padding:12px;border-radius:8px;" src="data:image/png;base64,' + pix.qr_code_base64 + '" />';
      }
      if (pix.qr_code) {
        html += '<p style="margin:16px 0 8px;font-size:12px;color:#9AA3C2;">Ou copie o código:</p>';
        html += '<textarea readonly style="width:100%;min-height:80px;font-size:11px;padding:8px;">' + pix.qr_code + '</textarea>';
      }
      if (pix.ticket_url) {
        html += '<p style="margin-top:14px;"><a href="' + pix.ticket_url + '" target="_blank" rel="noopener" style="color:#fff;text-decoration:underline;">Abrir página do Pix</a></p>';
      }
      if (redirectUrl) {
        html += '<p style="margin-top:18px;font-size:13px;"><a href="' + redirectUrl + '" style="color:#fff;text-decoration:underline;">Já paguei — ver status do pedido</a></p>';
      }
      html += '</div>';
      statusBox.innerHTML = html;
    }
    if (hint) hint.textContent = 'Escaneie o QR Code ou copie o código Pix para pagar.';
  }

  function showStatusScreen(paymentId, redirectUrl, pix) {
    if (pix && (pix.qr_code_base64 || pix.qr_code)) {
      showPixFallback(pix, redirectUrl);
      return;
    }

    if (window.paymentBrickController) {
      try { window.paymentBrickController.unmount(); } catch (e) {}
    }
    if (payBox) payBox.style.display = 'none';
    if (statusBox) statusBox.style.display = 'block';
    if (hint) hint.textContent = 'Aguardando confirmação do pagamento…';

    if (!paymentId) {
      if (redirectUrl) window.location.href = redirectUrl;
      return;
    }

    bricks.create('statusScreen', 'statusScreenBrick_container', {
      initialization: { paymentId: String(paymentId) },
      customization: { visual: { style: { theme: 'dark' } } },
      callbacks: {
        onReady: function () {},
        onError: function () {
          if (redirectUrl) window.location.href = redirectUrl;
        },
      },
    }).then(function (controller) {
      window.statusScreenBrickController = controller;
    });

    if (redirectUrl) {
      setTimeout(function () {
        if (hint) {
          hint.innerHTML = 'Quando concluir, <a href="' + redirectUrl + '" style="color:#fff;text-decoration:underline;">clique aqui para ver o status do pedido</a>.';
        }
      }, 4000);
    }
  }

  bricks.create('payment', 'paymentBrick_container', {
    initialization: {
      amount: amount,
      payer: {
        email: payerEmail,
        entityType: 'individual',
      },
    },
    customization: {
      visual: { style: { theme: 'dark' } },
      paymentMethods: {
        maxInstallments: 12,
        creditCard: 'all',
        debitCard: 'all',
        ticket: 'all',
        // Pix (Checkout Transparente). NÃO incluir mercadoPago sem preferenceId.
        bankTransfer: ['pix'],
      },
    },
    callbacks: {
      onReady: function () {},
      onError: function (error) {
        console.error('Payment Brick error', error);
        showError('Erro ao carregar o formulário de pagamento. Recarregue a página.');
      },
      onSubmit: function (payload) {
        const formData = payload && payload.formData ? Object.assign({}, payload.formData) : Object.assign({}, payload || {});
        if (payload && payload.selectedPaymentMethod) {
          formData.selected_payment_method = payload.selectedPaymentMethod;
        }
        return new Promise(function (resolve, reject) {
          fetch(processUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrf,
              'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(formData || {}),
          })
            .then(async function (response) {
              const data = await response.json().catch(function () { return {}; });
              if (!response.ok || !data.ok) {
                showError(data.error || 'Pagamento recusado. Tente outro método.');
                reject(data.error || 'payment_failed');
                return;
              }
              resolve();

              if (data.status === 'approved' && data.redirect) {
                window.location.href = data.redirect;
                return;
              }

              if ((data.status === 'pending' || data.status === 'in_process') && (data.payment_id || data.pix)) {
                showStatusScreen(data.payment_id, data.redirect, data.pix);
                return;
              }

              if (data.redirect) {
                window.location.href = data.redirect;
              }
            })
            .catch(function () {
              showError('Falha de conexão ao processar o pagamento.');
              reject();
            });
        });
      },
    },
  }).then(function (controller) {
    window.paymentBrickController = controller;
  }).catch(function (err) {
    console.error(err);
    showError('Não foi possível iniciar o checkout. Recarregue a página.');
  });
})();
</script>
@endpush
