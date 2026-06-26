<nav>
  <div class="nav-logo"><a href="{{ route('home') }}"><img src="{{ asset('assets/logos/logo_azul_chama.png') }}" alt="Convictos"></a></div>
  <div class="nav-links">
    <a href="{{ route('home') }}#sobre">Sobre</a>
    <a href="{{ route('home') }}#missao">Missão</a>
    <a href="{{ route('home') }}#valores">Valores</a>
    <a href="{{ route('store.index') }}" class="{{ request()->routeIs('store.*') ? 'active' : '' }}">Loja</a>
    <a href="{{ route('home') }}#inscricao" class="nav-cta">Inscreva-se</a>
  </div>
  <button class="nav-hamburger" onclick="document.getElementById('mobileMenu').classList.toggle('open')" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
</nav>

<div class="mobile-menu" id="mobileMenu">
  <a href="{{ route('home') }}#sobre" onclick="document.getElementById('mobileMenu').classList.remove('open')">Sobre</a>
  <a href="{{ route('home') }}#missao" onclick="document.getElementById('mobileMenu').classList.remove('open')">Missão</a>
  <a href="{{ route('home') }}#valores" onclick="document.getElementById('mobileMenu').classList.remove('open')">Valores</a>
  <a href="{{ route('store.index') }}" onclick="document.getElementById('mobileMenu').classList.remove('open')">Loja</a>
  <a href="{{ route('home') }}#inscricao" onclick="document.getElementById('mobileMenu').classList.remove('open')">Inscreva-se</a>
</div>
