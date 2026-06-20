<x-mail::message>
# Inscrição recebida! 🔥

Olá, **{{ $inscricao->nome }}**!

Recebemos a sua inscrição para a conferência **Convictos UM 2027**. Que alegria ter você com a gente!

Em breve nossa equipe entrará em contato com mais informações.

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
