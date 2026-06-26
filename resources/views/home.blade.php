@extends('layouts.site')

@section('title', 'CONVICTOS UM 2027 — Conferência de Jovens')

@section('content')
@if($dataEvento)
<style>
  .countdown-float {
    position: fixed; right: 18px; bottom: 18px; z-index: 900;
    background: linear-gradient(135deg, #0b1f4b, #15306e);
    color: #fff; border-radius: 14px; padding: 12px 14px;
    box-shadow: 0 12px 30px rgba(0,0,0,.35); border: 1px solid rgba(255,255,255,.08);
    font-family: 'Oswald', sans-serif; text-align: center; animation: cdfloat 3s ease-in-out infinite;
  }
  @keyframes cdfloat { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
  .countdown-label { font-size: .62rem; letter-spacing: 1.5px; text-transform: uppercase; color: #f59e0b; font-weight: 700; margin-bottom: 6px; }
  .countdown-grid { display: flex; gap: 8px; }
  .countdown-box { display: flex; flex-direction: column; min-width: 38px; background: rgba(255,255,255,.06); border-radius: 8px; padding: 5px 4px; }
  .countdown-num { font-size: 1.25rem; font-weight: 700; line-height: 1; }
  .countdown-unit { font-size: .58rem; text-transform: uppercase; letter-spacing: .5px; color: #cbd5e1; margin-top: 2px; }
  @media (max-width: 600px) {
    .countdown-float { right: 10px; bottom: 10px; padding: 9px 10px; }
    .countdown-box { min-width: 32px; }
    .countdown-num { font-size: 1.05rem; }
  }
</style>
<div class="countdown-float" id="countdown" data-event="{{ $dataEvento->format('Y-m-d\TH:i:s') }}" aria-live="polite">
  <div class="countdown-label">Contagem regressiva</div>
  <div class="countdown-grid">
    <div class="countdown-box"><span class="countdown-num" data-cd="days">--</span><span class="countdown-unit">dias</span></div>
    <div class="countdown-box"><span class="countdown-num" data-cd="hours">--</span><span class="countdown-unit">h</span></div>
    <div class="countdown-box"><span class="countdown-num" data-cd="mins">--</span><span class="countdown-unit">min</span></div>
    <div class="countdown-box"><span class="countdown-num" data-cd="secs">--</span><span class="countdown-unit">s</span></div>
  </div>
</div>
@endif

<!-- HERO -->
<div class="hero">
  <img src="{{ asset('assets/logos/chama-cor.png') }}" alt="" class="hero-flame">
  <img src="{{ asset('assets/logos/chama-cor.png') }}" alt="" class="hero-flame2">
  <div class="hero-content">
    <span class="hero-eyebrow">Conferência de Jovens · Assembleia de Deus - Ministério Madureira · Luziânia</span>
    <img src="{{ asset('assets/logos/um-escudo-azul.png') }}" alt="CONVICTOS UM 2027" class="hero-logo">
    <div class="hero-banner">
      <span>UMA GERAÇÃO QUE <span class="red">NÃO RECUA</span></span>
    </div>
    <p class="hero-verse">"Para que todos sejam um." — <strong>João 17:21</strong></p>
    <div class="btn-row">
      <a href="#inscricao" class="btn-primary">Faça sua inscrição</a>
      <a href="#sobre" class="btn-outline">Saiba Mais</a>
    </div>
  </div>
  <div class="hero-scroll">
    <div class="scroll-line"></div>
    <span class="scroll-label">Rolar</span>
  </div>
</div>

<!-- SOBRE -->
<section class="sobre" id="sobre">
  <div class="sobre-grid">
    <div class="sobre-text">
      <span class="label">O Contexto</span>
      <h2 class="title">O PONTO<br><span class="stroke">CRÍTICO</span></h2>
      <p>O carnaval não é apenas um feriado. É o período de maior vulnerabilidade para a nossa juventude — e exige uma resposta intencional.</p>
      <p>Convictos é um movimento cristão voltado para jovens que promove encontros, conferências e experiências de fé, inspirando uma geração a viver com propósito, identidade em Cristo e convicção dos princípios do Reino de Deus.</p>
    </div>
    <div class="sobre-cards">
      <div class="threat-card">
        <span class="threat-num">01</span>
        <div><div class="threat-title">A Ameaça</div><div class="threat-desc">Festividades seculares intensas que dominam o período de carnaval.</div></div>
      </div>
      <div class="threat-card">
        <span class="threat-num">02</span>
        <div><div class="threat-title">O Risco</div><div class="threat-desc">Exposição e atração às drogas e aos vícios durante a festividade.</div></div>
      </div>
      <div class="threat-card">
        <span class="threat-num">03</span>
        <div><div class="threat-title">A Consequência</div><div class="threat-desc">Perda de foco espiritual, isolamento e perda de identidade.</div></div>
      </div>
    </div>
  </div>
</section>

<!-- PILARES -->
<section class="pilares" id="missao">
  <div class="pilares-inner">
    <div class="pilares-header">
      <div>
        <span class="label">Nossa Missão</span>
        <h2 class="title">UM REFÚGIO<br><span class="stroke">ESTRATÉGICO</span></h2>
      </div>
      <p>Reunir a juventude no período de Carnaval para criar um ambiente impenetrável de adoração e exaltação a Cristo.</p>
    </div>
    <div class="pilares-grid">
      <div class="pilar-card"><div class="pilar-num">01</div><div class="pilar-line"></div><div class="pilar-title">Adoração</div><div class="pilar-desc">Exaltar o nome de Jesus no momento de maior distração do mundo.</div></div>
      <div class="pilar-card"><div class="pilar-num">02</div><div class="pilar-line"></div><div class="pilar-title">Palavra</div><div class="pilar-desc">Ensino profundo e direcionado para a geração convicta.</div></div>
      <div class="pilar-card"><div class="pilar-num">03</div><div class="pilar-line"></div><div class="pilar-title">Ministração</div><div class="pilar-desc">Transformação espiritual ativa — não apenas assistir, mas experienciar.</div></div>
      <div class="pilar-card"><div class="pilar-num">04</div><div class="pilar-line"></div><div class="pilar-title">Comunhão</div><div class="pilar-desc">Fortalecimento através da união e de amizades verdadeiras.</div></div>
    </div>
  </div>
</section>

<!-- DOIS CAMINHOS -->
<section class="caminhos">
  <div class="caminhos-inner">
    <div class="caminhos-header">
      <span class="label">A Escolha</span>
      <h2 class="title">DOIS CAMINHOS</h2>
    </div>
    <div class="caminhos-grid">
      <div class="caminho-col caminho-mundo">
        <div class="caminho-titulo">O MUNDO</div>
        <div class="caminho-row"><span class="caminho-label">Foco Central</span><span class="caminho-val">Festividades seculares e distrações.</span></div>
        <div class="caminho-row"><span class="caminho-label">Ambiente</span><span class="caminho-val">Exposição a vícios, drogas e influências destrutivas.</span></div>
        <div class="caminho-row"><span class="caminho-label">Conexão</span><span class="caminho-val">Isolamento espiritual e amizades superficiais.</span></div>
        <div class="caminho-row"><span class="caminho-label">Resultado</span><span class="caminho-val">Vazio e perda de identidade.</span></div>
      </div>
      <div class="caminho-col caminho-convictos">
        <div class="caminho-bar"></div>
        <div class="caminho-titulo">CONVICTOS</div>
        <div class="caminho-row"><span class="caminho-label">Foco Central</span><span class="caminho-val">Adoração e exaltação a Jesus.</span></div>
        <div class="caminho-row"><span class="caminho-label">Ambiente</span><span class="caminho-val">Santuário de ministração e ensino da Palavra.</span></div>
        <div class="caminho-row"><span class="caminho-label">Conexão</span><span class="caminho-val">Comunhão profunda e verdadeira.</span></div>
        <div class="caminho-row"><span class="caminho-label">Resultado</span><span class="caminho-val">Certeza de propósito e renovação.</span></div>
      </div>
    </div>
  </div>
</section>

<!-- VALORES -->
<section class="valores" id="valores">
  <div class="valores-inner">
    <span class="label">Nosso Fundamento</span>
    <h2 class="title">A BASE<br><span class="stroke">INABALÁVEL</span></h2>
    <div class="quote-block">
      <span class="quote-mark">"</span>
      <p class="quote-text">Nossos valores não são negociáveis. Tudo o que construímos está fundamentado unicamente naquilo que Cristo deixou para nós.</p>
    </div>
    <div class="valores-cards">
      <div class="valor-card"><div class="valor-tag"></div><div class="valor-title">O FUNDAMENTO</div><div class="valor-desc">A Palavra de Deus — imutável, viva e eficaz. Nossa única âncora.</div></div>
      <div class="valor-card valor-card-accent"><div class="valor-tag"></div><div class="valor-title">O ALICERCE</div><div class="valor-desc">Os ensinamentos deixados por Cristo — o único que nunca falha.</div></div>
    </div>
  </div>
</section>

<!-- GERAÇÃO -->
<section class="geracao">
  <div class="geracao-inner">
    <div class="geracao-header">
      <span class="label">O Alvo</span>
      <h2 class="title">GERAÇÃO<br><span class="stroke">CONVICTA</span></h2>
    </div>
    <div class="geracao-grid">
      <div class="geracao-item">
        <div class="geracao-icon">P</div>
        <div class="geracao-label">PROPÓSITO</div>
        <div class="geracao-desc">A razão pela qual fomos criados — viver com intenção e direção definidas por Deus.</div>
      </div>
      <div class="geracao-center-box">
        <div class="geracao-center-super">CONVICTOS · UM</div>
        <div class="geracao-center-title">GERAÇÃO<br>CONVICTA</div>
        <p class="geracao-center-desc">Levantar uma juventude com o coração voltado inteiramente para a Palavra, blindada contra as dúvidas do mundo.</p>
        <div class="geracao-center-ref">João 17:21</div>
      </div>
      <div class="geracao-right">
        <div class="geracao-item">
          <div class="geracao-icon">C</div>
          <div class="geracao-label">CHAMADO</div>
          <div class="geracao-desc">A missão específica confiada a cada jovem — única, irrepetível e urgente.</div>
        </div>
        <div class="geracao-item">
          <div class="geracao-icon">I</div>
          <div class="geracao-label">IDENTIDADE</div>
          <div class="geracao-desc">A certeza inabalável de quem são em Cristo — não no que o mundo define.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA / INSCRIÇÃO -->
<section class="cta-section" id="inscricao">
  <img src="{{ asset('assets/logos/chama-cor.png') }}" alt="" class="cta-bg-flame">
  <div class="cta-inner">
    <span class="label">Inscrição</span>
    <div class="cta-title">SEJA<br>CONVICTO</div>
    <div class="cta-verse">"Para que todos sejam um."</div>
    <p class="cta-sub">Garanta sua vaga na Conferência Convictos UM 2027. Após o cadastro, você receberá orientações no WhatsApp.</p>

    @if(session('inscricao_success'))
      <div class="flash flash-success" style="position:static;margin:0 0 20px;">{{ session('inscricao_success') }}</div>
    @endif

    @if($errors->any())
      <div class="flash flash-error" style="position:static;margin:0 0 20px;">
        @foreach($errors->all() as $error){{ $error }}<br>@endforeach
      </div>
    @endif

    <form method="POST" action="{{ route('inscricao.store') }}" id="form-inscricao">
      @csrf
      <div class="form-group">
        <input type="text" name="nome" class="form-input" placeholder="Seu nome completo" value="{{ old('nome') }}" required>
        <input type="email" name="email" class="form-input" placeholder="E-mail (opcional)" value="{{ old('email') }}">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <input type="number" name="idade" class="form-input" placeholder="Idade" min="10" max="120" value="{{ old('idade') }}" required>
          <input type="tel" name="whatsapp" class="form-input" placeholder="(99) 99999-9999" inputmode="numeric" maxlength="15" data-phone value="{{ old('whatsapp') }}" required>
        </div>
        <select name="tamanho_camiseta" class="form-input" required>
          <option value="" disabled {{ old('tamanho_camiseta') ? '' : 'selected' }}>Tamanho da camiseta oficial</option>
          @foreach(\App\Models\Inscricao::tamanhoCamisetaOptions() as $value => $label)
            <option value="{{ $value }}" @selected(old('tamanho_camiseta') === $value)>{{ $label }}</option>
          @endforeach
        </select>
        @if($igrejas->isEmpty())
          <select name="igreja_id" class="form-input" disabled>
            <option value="">Nenhuma igreja cadastrada ainda</option>
          </select>
          <p class="cta-note" style="margin-top:8px;">As igrejas são cadastradas pela equipe no painel administrativo.</p>
        @else
          <select name="igreja_id" class="form-input" required>
            <option value="" disabled {{ old('igreja_id') ? '' : 'selected' }}>Selecione sua igreja</option>
            @foreach($igrejas as $igreja)
              <option value="{{ $igreja->id }}" @selected((string) old('igreja_id') === (string) $igreja->id)>{{ $igreja->bairro }}</option>
            @endforeach
          </select>
        @endif
        <fieldset style="border:none;padding:0;margin:0;">
          <legend style="font-size:0.85rem;color:rgba(255,255,255,0.7);margin-bottom:8px;">É líder de jovens?</legend>
          <div style="display:flex;gap:16px;">
            <label style="display:flex;align-items:center;gap:6px;color:#fff;font-size:0.9rem;">
              <input type="radio" name="lider" value="sim" @checked(old('lider') === 'sim') required> Sim
            </label>
            <label style="display:flex;align-items:center;gap:6px;color:#fff;font-size:0.9rem;">
              <input type="radio" name="lider" value="nao" @checked(old('lider', 'nao') === 'nao')> Não
            </label>
          </div>
        </fieldset>
      </div>
      <button class="form-submit" type="submit" @if($igrejas->isEmpty()) disabled @endif>Fazer minha inscrição</button>
    </form>

    @push('scripts')
    <script>
      (function () {
        var form = document.getElementById('form-inscricao');
        if (!form) return;

        form.addEventListener('submit', function () {
          if (form.dataset.enviando === '1') return;
          form.dataset.enviando = '1';

          var btn = form.querySelector('.form-submit');
          if (btn) {
            btn.disabled = true;
            btn.dataset.original = btn.textContent;
            btn.textContent = 'Enviando, aguarde...';
            btn.style.opacity = '0.7';
            btn.style.cursor = 'wait';
          }
        });
      })();
    </script>
    @endpush

    @push('scripts')
    <script>
      (function () {
        var el = document.getElementById('countdown');
        if (!el) return;

        var alvo = new Date(el.getAttribute('data-event')).getTime();
        if (isNaN(alvo)) { el.style.display = 'none'; return; }

        var set = function (key, val) {
          var node = el.querySelector('[data-cd="' + key + '"]');
          if (node) node.textContent = val;
        };

        var tick = function () {
          var resta = alvo - Date.now();

          if (resta <= 0) {
            set('days', '0'); set('hours', '00'); set('mins', '00'); set('secs', '00');
            el.querySelector('.countdown-label').textContent = 'É hoje! 🔥';
            clearInterval(timer);
            return;
          }

          var dias = Math.floor(resta / 86400000);
          var horas = Math.floor((resta % 86400000) / 3600000);
          var mins = Math.floor((resta % 3600000) / 60000);
          var secs = Math.floor((resta % 60000) / 1000);

          var pad = function (n) { return (n < 10 ? '0' : '') + n; };
          set('days', dias);
          set('hours', pad(horas));
          set('mins', pad(mins));
          set('secs', pad(secs));
        };

        tick();
        var timer = setInterval(tick, 1000);
      })();
    </script>
    @endpush
    <p class="cta-note">Sem spam. Só avisamos sobre o que importa — a Conferência Definitiva.</p>
    <div class="social-links">
      <a href="#" class="social-link">Instagram</a>
      <a href="#" class="social-link">YouTube</a>
      <a href="https://wa.me/{{ config('services.loja.whatsapp') }}" class="social-link">WhatsApp</a>
    </div>
  </div>
</section>
@endsection
