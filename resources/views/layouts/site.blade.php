<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'CONVICTOS UM 2027 — Conferência de Jovens')</title>
<meta name="description" content="@yield('description', 'Conferência jovem cristã da Juventude Mais — Assembleia de Deus, Ministério Madureira, Luziânia. Uma geração que não recua.')">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" href="{{ asset('assets/favicon.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Anton&family=DM+Sans:wght@300;400;500;600&family=Oswald:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/store.css') }}">
</head>
<body>

@include('partials.nav')

@if (session('success') || session('error'))
  <div class="flash {{ session('error') ? 'flash-error' : 'flash-success' }}">
    {{ session('success') ?? session('error') }}
  </div>
@endif

@yield('content')

@include('partials.footer')

<script src="{{ asset('js/script.js') }}"></script>
@stack('scripts')
</body>
</html>
