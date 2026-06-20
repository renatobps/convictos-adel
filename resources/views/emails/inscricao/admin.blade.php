<x-mail::message>
# Novo lead capturado

Um novo contato se cadastrou no site.

- **Nome:** {{ $inscricao->nome }}
- **E-mail:** {{ $inscricao->email }}
- **WhatsApp:** {{ $inscricao->whatsapp ?: '—' }}
- **Cidade:** {{ $inscricao->cidade ?: '—' }}
- **Igreja:** {{ $inscricao->igreja ?: '—' }}
- **Data:** {{ $inscricao->created_at->format('d/m/Y H:i') }}

<x-mail::button :url="url('/admin/inscricaos/' . $inscricao->id . '/edit')">
Ver no painel
</x-mail::button>
</x-mail::message>
