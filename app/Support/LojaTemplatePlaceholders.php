<?php

namespace App\Support;

use App\Models\Order;

class LojaTemplatePlaceholders
{
    /**
     * @return array<string, string>
     */
    public static function descricaoPlaceholders(): array
    {
        return [
            '{nome_cliente}' => 'Nome do cliente',
            '{email_cliente}' => 'E-mail do cliente',
            '{telefone_cliente}' => 'Telefone / WhatsApp',
            '{referencia_pedido}' => 'Referência do pedido (ex.: CV-ABC123)',
            '{total_pedido}' => 'Total formatado (R$ 99,90)',
            '{status_pedido}' => 'Status atual do pedido',
            '{status_anterior}' => 'Status anterior (em alterações)',
            '{status_mensagem}' => 'Mensagem contextual do status',
            '{local_retirada}' => 'Local de retirada (nome curto)',
            '{nome_local}' => 'Nome completo do local (CADEL)',
            '{endereco_retirada}' => 'Endereço completo do local',
            '{link_maps}' => 'Link do Google Maps',
            '{instrucoes_retirada}' => 'Instruções de retirada',
            '{horarios_retirada}' => 'Horários de retirada (lista)',
            '{itens_pedido}' => 'Lista dos itens do pedido',
            '{observacoes}' => 'Observações do cliente',
            '{forma_pagamento}' => 'Forma de pagamento',
        ];
    }

    public static function textoPlaceholders(): string
    {
        return collect(self::descricaoPlaceholders())
            ->map(fn (string $desc, string $key): string => $key.' — '.$desc)
            ->implode(', ');
    }

    public static function substituir(string $template, Order $order, ?string $statusAnterior = null): string
    {
        return strtr($template, self::mapa($order, $statusAnterior));
    }

    /**
     * @return array<string, string>
     */
    public static function mapa(Order $order, ?string $statusAnterior = null): array
    {
        $order->loadMissing(['items.product']);

        $local = LojaRetiradaConfig::localizacao();
        $formaPagamento = match ($order->payment_method) {
            'mercadopago' => 'MercadoPago',
            'pix' => 'PIX',
            'manual' => 'Manual / Combinar',
            default => $order->payment_method ?: '—',
        };

        return [
            '{nome_cliente}' => (string) $order->customer_name,
            '{email_cliente}' => (string) $order->customer_email,
            '{telefone_cliente}' => (string) ($order->customer_phone ?: '—'),
            '{referencia_pedido}' => (string) $order->reference,
            '{total_pedido}' => 'R$ '.number_format((float) $order->total, 2, ',', '.'),
            '{status_pedido}' => (string) $order->status_label,
            '{status_anterior}' => $statusAnterior !== null
                ? (string) (Order::STATUSES[$statusAnterior] ?? $statusAnterior)
                : '—',
            '{status_mensagem}' => (string) $order->mensagemStatusCliente(),
            '{local_retirada}' => LojaRetiradaConfig::local(),
            '{nome_local}' => $local['name'],
            '{endereco_retirada}' => $local['address'],
            '{link_maps}' => LojaRetiradaConfig::linkGoogleMaps(),
            '{instrucoes_retirada}' => LojaRetiradaConfig::instrucoes(),
            '{horarios_retirada}' => LojaRetiradaConfig::textoHorariosResumido(),
            '{itens_pedido}' => self::formatarItens($order),
            '{observacoes}' => trim((string) ($order->notes ?: '—')),
            '{forma_pagamento}' => $formaPagamento,
        ];
    }

    public static function formatarItens(Order $order): string
    {
        return $order->items
            ->map(function ($item): string {
                $linha = '• '.$item->quantity.'× '.$item->product_name;
                if ($item->size) {
                    $linha .= ' ('.$item->size.')';
                }

                return $linha.' — R$ '.number_format((float) $item->subtotal, 2, ',', '.');
            })
            ->implode("\n");
    }

    /**
     * @return array<int, array{url: string, nome: string, quantidade: int, filename: string}>
     */
    public static function imagensProdutos(Order $order): array
    {
        $order->loadMissing(['items.product']);

        $imagens = [];

        foreach ($order->items as $item) {
            $product = $item->product;
            if ($product === null || blank($product->image)) {
                continue;
            }

            $url = MidiaPublica::urlPublica((string) $product->image);
            if ($url === '') {
                continue;
            }

            $path = MidiaPublica::caminhoLocal((string) $product->image);

            $imagens[] = [
                'url' => $url,
                'path' => $path,
                'nome' => (string) $item->product_name,
                'quantidade' => (int) $item->quantity,
                'filename' => \Illuminate\Support\Str::slug($item->product_name).'.jpg',
            ];
        }

        return $imagens;
    }

    public static function urlAbsoluta(string $url): string
    {
        return MidiaPublica::urlAbsoluta($url);
    }
}
