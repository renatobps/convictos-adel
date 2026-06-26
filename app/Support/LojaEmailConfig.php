<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class LojaEmailConfig
{
    public const ADMIN_NOVO_PEDIDO = LojaNotificacaoConfig::ADMIN_NOVO_PEDIDO;

    public const CLIENTE_PEDIDO_REGISTRADO = LojaNotificacaoConfig::CLIENTE_PEDIDO_REGISTRADO;

    public const CLIENTE_EM_SEPARACAO = LojaNotificacaoConfig::CLIENTE_EM_SEPARACAO;

    public const CLIENTE_PRONTO_RETIRADA = LojaNotificacaoConfig::CLIENTE_PRONTO_RETIRADA;

    public const CLIENTE_RETIRADO = LojaNotificacaoConfig::CLIENTE_RETIRADO;

    public const CLIENTE_CANCELADO = LojaNotificacaoConfig::CLIENTE_CANCELADO;

    /**
     * @return array<string, string>
     */
    public static function eventos(): array
    {
        return LojaNotificacaoConfig::eventos();
    }

    public static function eventoPorStatus(string $status): ?string
    {
        return LojaNotificacaoConfig::eventoPorStatus($status);
    }

    /**
     * @return array<string, mixed>
     */
    public static function template(string $evento): array
    {
        return self::lerTemplateBruto($evento);
    }

    /**
     * @return array<string, mixed>
     */
    public static function templateParaFormulario(string $evento): array
    {
        $template = self::lerTemplateBruto($evento);
        $template['imagem'] = self::imagemParaFormulario($template['imagem'] ?? null);

        return $template;
    }

    /**
     * @param  array<string, mixed>  $template
     */
    public static function salvarTemplate(string $evento, array $template): void
    {
        $payload = self::ler();
        $imagem = self::normalizarImagem($template['imagem'] ?? null);
        $imagemAtual = self::normalizarImagem(self::lerTemplateBruto($evento)['imagem'] ?? null);

        if ($imagem !== null && self::isCaminhoTemporario($imagem)) {
            $imagem = $imagemAtual;
        }

        $payload[$evento] = array_merge(self::lerTemplateBruto($evento), [
            'ativo' => (bool) ($template['ativo'] ?? true),
            'assunto' => trim((string) ($template['assunto'] ?? '')),
            'conteudo' => (string) ($template['conteudo'] ?? ''),
            'imagem' => $imagem ?? '',
            'enviar_imagem_produto' => (bool) ($template['enviar_imagem_produto'] ?? false),
            'botao_texto' => trim((string) ($template['botao_texto'] ?? '')),
            'botao_url' => trim((string) ($template['botao_url'] ?? '')),
        ]);
        self::escrever($payload);
    }

    public static function templateAtivo(string $evento): bool
    {
        return (bool) (self::template($evento)['ativo'] ?? true);
    }

    public static function enviarImagemProduto(string $evento): bool
    {
        return (bool) (self::template($evento)['enviar_imagem_produto'] ?? false);
    }

    public static function imagemUrl(string $evento): string
    {
        return MidiaPublica::urlPublica(self::imagemReferencia($evento));
    }

    public static function imagemReferencia(string $evento): string
    {
        $imagem = self::normalizarImagem(self::lerTemplateBruto($evento)['imagem'] ?? null);

        return $imagem ?? '';
    }

    public static function assuntoRenderizado(string $evento, Order $order, ?string $statusAnterior = null): string
    {
        $assunto = trim((string) (self::template($evento)['assunto'] ?? ''));

        return LojaTemplatePlaceholders::substituir(
            $assunto !== '' ? $assunto : (string) (self::templatePadrao($evento)['assunto'] ?? ''),
            $order,
            $statusAnterior
        );
    }

    public static function conteudoRenderizado(string $evento, Order $order, ?string $statusAnterior = null): string
    {
        $conteudo = (string) (self::template($evento)['conteudo'] ?? '');

        $html = LojaTemplatePlaceholders::substituir(
            $conteudo !== '' ? $conteudo : (string) (self::templatePadrao($evento)['conteudo'] ?? ''),
            $order,
            $statusAnterior
        );

        return str_replace("\n", '<br>', $html);
    }

    /**
     * @return array<string, string>|null
     */
    public static function imagemParaFormulario(mixed $imagem): ?array
    {
        $normalizada = self::normalizarImagem($imagem);

        if (blank($normalizada)) {
            return null;
        }

        return [(string) Str::uuid() => $normalizada];
    }

    public static function normalizarImagem(mixed $imagem): ?string
    {
        if ($imagem instanceof TemporaryUploadedFile) {
            return null;
        }

        if (is_array($imagem)) {
            $imagem = collect($imagem)
                ->flatten()
                ->first(fn (mixed $valor): bool => filled($valor));
        }

        if (blank($imagem) || $imagem instanceof TemporaryUploadedFile) {
            return null;
        }

        $caminho = (string) $imagem;

        if (self::isCaminhoTemporario($caminho)) {
            return null;
        }

        return $caminho;
    }

    private static function isCaminhoTemporario(string $caminho): bool
    {
        if (str_contains($caminho, sys_get_temp_dir())) {
            return true;
        }

        return (bool) preg_match('/php[A-F0-9]+\.tmp$/i', $caminho);
    }

    /**
     * @return array<string, mixed>
     */
    private static function lerTemplateBruto(string $evento): array
    {
        $data = self::ler();
        $tpl = is_array($data[$evento] ?? null) ? $data[$evento] : [];
        $merged = array_merge(self::templatePadrao($evento), $tpl);
        $merged['imagem'] = self::normalizarImagem($merged['imagem'] ?? null) ?? '';

        return $merged;
    }

    /**
     * @return array<string, mixed>
     */
    private static function templatePadrao(string $evento): array
    {
        $base = [
            'ativo' => true,
            'imagem' => null,
            'enviar_imagem_produto' => true,
            'botao_texto' => 'Acessar a loja',
            'botao_url' => (string) config('app.url').'/loja',
        ];

        return match ($evento) {
            self::ADMIN_NOVO_PEDIDO => array_merge($base, [
                'assunto' => 'Novo pedido {referencia_pedido} — {total_pedido}',
                'conteudo' => '<p>Novo pedido na loja <strong>{referencia_pedido}</strong>.</p>'
                    .'<p><strong>Cliente:</strong> {nome_cliente}<br>'
                    .'<strong>E-mail:</strong> {email_cliente}<br>'
                    .'<strong>WhatsApp:</strong> {telefone_cliente}<br>'
                    .'<strong>Total:</strong> {total_pedido}<br>'
                    .'<strong>Status:</strong> {status_pedido}</p>'
                    .'<p><strong>Itens:</strong><br>{itens_pedido}</p>'
                    .'<p><strong>Retirada:</strong> {local_retirada}<br>{instrucoes_retirada}<br>{horarios_retirada}</p>'
                    .'<p><strong>Observações:</strong> {observacoes}</p>',
                'enviar_imagem_produto' => true,
                'botao_texto' => 'Ver no painel',
                'botao_url' => (string) config('app.url').'/admin/orders',
            ]),
            self::CLIENTE_PEDIDO_REGISTRADO => array_merge($base, [
                'assunto' => 'Pedido {referencia_pedido} registrado — Loja Convictos',
                'conteudo' => '<p>Olá, <strong>{nome_cliente}</strong>! 🙌</p>'
                    .'<p>Seu pedido <strong>{referencia_pedido}</strong> foi registrado.</p>'
                    .'<p><strong>Total:</strong> {total_pedido}</p>'
                    .'<p><strong>Itens:</strong><br>{itens_pedido}</p>'
                    .'<p><strong>Retirada:</strong> {local_retirada}<br>{instrucoes_retirada}<br>{horarios_retirada}</p>'
                    .'<p>Aguardamos a confirmação do pagamento.</p>',
            ]),
            self::CLIENTE_EM_SEPARACAO => array_merge($base, [
                'assunto' => 'Pedido {referencia_pedido} — {status_pedido}',
                'conteudo' => '<p>Olá, <strong>{nome_cliente}</strong>! 🙌</p>'
                    .'<p>Atualização do pedido <strong>{referencia_pedido}</strong>.</p>'
                    .'<p><strong>Status:</strong> {status_anterior} → <strong>{status_pedido}</strong></p>'
                    .'<p>{status_mensagem}</p>'
                    .'<p><strong>Itens:</strong><br>{itens_pedido}</p>'
                    .'<p><strong>Retirada:</strong> {local_retirada}<br>{instrucoes_retirada}<br>{horarios_retirada}</p>',
            ]),
            self::CLIENTE_PRONTO_RETIRADA => array_merge($base, [
                'assunto' => 'Pedido {referencia_pedido} pronto para retirada',
                'conteudo' => '<p>Olá, <strong>{nome_cliente}</strong>! 🙌</p>'
                    .'<p>Seu pedido <strong>{referencia_pedido}</strong> está <strong>pronto para retirada</strong>!</p>'
                    .'<p>{status_mensagem}</p>'
                    .'<p><strong>Itens:</strong><br>{itens_pedido}</p>'
                    .'<p><strong>Retirada:</strong> {local_retirada}<br>{instrucoes_retirada}<br>{horarios_retirada}</p>'
                    .'<p>Apresente este e-mail ou a mensagem do WhatsApp no momento da retirada.</p>',
            ]),
            self::CLIENTE_RETIRADO => array_merge($base, [
                'assunto' => 'Pedido {referencia_pedido} retirado',
                'conteudo' => '<p>Olá, <strong>{nome_cliente}</strong>! 🙌</p>'
                    .'<p>Confirmamos a retirada do pedido <strong>{referencia_pedido}</strong>.</p>'
                    .'<p>{status_mensagem}</p>'
                    .'<p>Obrigado por vestir a convicção! 🔥</p>',
                'enviar_imagem_produto' => false,
            ]),
            self::CLIENTE_CANCELADO => array_merge($base, [
                'assunto' => 'Pedido {referencia_pedido} cancelado',
                'conteudo' => '<p>Olá, <strong>{nome_cliente}</strong>.</p>'
                    .'<p>O pedido <strong>{referencia_pedido}</strong> foi cancelado.</p>'
                    .'<p>{status_mensagem}</p>'
                    .'<p>Em caso de dúvidas, fale conosco.</p>',
                'enviar_imagem_produto' => false,
            ]),
            default => array_merge($base, [
                'assunto' => 'Pedido {referencia_pedido}',
                'conteudo' => '<p>Atualização do pedido {referencia_pedido}.</p>',
                'enviar_imagem_produto' => false,
            ]),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function ler(): array
    {
        $path = self::path();
        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode((string) File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function escrever(array $payload): void
    {
        File::put(self::path(), json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private static function path(): string
    {
        return storage_path('app/loja-email-templates.json');
    }
}
