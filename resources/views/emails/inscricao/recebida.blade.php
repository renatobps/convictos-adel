<x-mail::message>
# Cadastro recebido! 🔥

Olá, **{{ $inscricao->nome }}**!

Recebemos o seu contato. Você vai ser um dos primeiros a saber das novidades do **Convictos UM 2027** — incluindo a abertura das inscrições.

Fique de olho no seu e-mail e WhatsApp!

@if($inscricao->cidade || $inscricao->igreja)
**Seus dados:**
@if($inscricao->cidade)- Cidade: {{ $inscricao->cidade }}@endif
@if($inscricao->igreja)
- Igreja: {{ $inscricao->igreja }}
@endif
@endif

> "Para que todos sejam um." — João 17:21

Uma geração que não recua.

<x-mail::button :url="config('app.url')">
Acessar o site
</x-mail::button>

Abraço,<br>
**Equipe Convictos**
</x-mail::message>
