<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\File;

class LojaNotificacaoConfig
{
    public const ADMIN_NOVO_PEDIDO = 'admin_novo_pedido';

    public const CLIENTE_PEDIDO_REGISTRADO = 'cliente_pedido_registrado';

    public const CLIENTE_EM_SEPARACAO = 'cliente_em_separacao';

    public const CLIENTE_PRONTO_RETIRADA = 'cliente_pronto_retirada';

    public const CLIENTE_RETIRADO = 'cliente_retirado';

    public const CLIENTE_CANCELADO = 'cliente_cancelado';

    /**
     * @return array<string, string>
     */
    public static function eventos(): array
    {
        return [
            self::ADMIN_NOVO_PEDIDO => 'Admin — Novo pedido',
            self::CLIENTE_PEDIDO_REGISTRADO => 'Cliente — Pedido registrado',
            self::CLIENTE_EM_SEPARACAO => 'Cliente — Pedido em separação',
            self::CLIENTE_PRONTO_RETIRADA => 'Cliente — Pronto para retirada',
            self::CLIENTE_RETIRADO => 'Cliente — Retirado',
            self::CLIENTE_CANCELADO => 'Cliente — Cancelado',
        ];
    }

    public static function eventoPorStatus(string $status): ?string
    {
        return match ($status) {
            'em_separacao' => self::CLIENTE_EM_SEPARACAO,
            'pronto_retirada' => self::CLIENTE_PRONTO_RETIRADA,
            'retirado' => self::CLIENTE_RETIRADO,
            'cancelado' => self::CLIENTE_CANCELADO,
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function template(string $evento): array
    {
        $data = self::ler();
        $tpl = is_array($data[$evento] ?? null) ? $data[$evento] : [];

        return array_merge(self::templatePadrao($evento), $tpl);
    }

    /**
     * @return array<string, mixed>
     */
    public static function templateParaFormulario(string $evento): array
    {
        return self::template($evento);
    }

    /**
     * @param  array<string, mixed>  $template
     */
    public static function salvarTemplate(string $evento, array $template): void
    {
        $payload = self::ler();
        $payload[$evento] = array_merge(self::templatePadrao($evento), [
            'mensagem' => trim((string) ($template['mensagem'] ?? '')),
            'enviar_imagem_produto' => (bool) ($template['enviar_imagem_produto'] ?? false),
            'enviar_localizacao' => (bool) ($template['enviar_localizacao'] ?? false),
            'imagem_url' => trim((string) ($template['imagem_url'] ?? '')),
        ]);
        self::escrever($payload);
    }

    public static function mensagem(string $evento): string
    {
        $mensagem = trim((string) (self::template($evento)['mensagem'] ?? ''));

        return $mensagem !== '' ? $mensagem : (string) (self::templatePadrao($evento)['mensagem'] ?? '');
    }

    public static function enviarImagemProduto(string $evento): bool
    {
        return (bool) (self::template($evento)['enviar_imagem_produto'] ?? false);
    }

    public static function enviarLocalizacao(string $evento): bool
    {
        return (bool) (self::template($evento)['enviar_localizacao'] ?? false);
    }

    public static function imagemUrl(string $evento): string
    {
        return trim((string) (self::template($evento)['imagem_url'] ?? ''));
    }

    public static function mensagemRenderizada(string $evento, Order $order, ?string $statusAnterior = null): string
    {
        return LojaTemplatePlaceholders::substituir(self::mensagem($evento), $order, $statusAnterior);
    }

    /**
     * @return array<string, mixed>
     */
    private static function templatePadrao(string $evento): array
    {
        return match ($evento) {
            self::ADMIN_NOVO_PEDIDO => [
                'mensagem' => "🛒 *Novo pedido na loja*\n\n"
                    ."*Pedido:* {referencia_pedido}\n"
                    ."*Cliente:* {nome_cliente}\n"
                    ."*E-mail:* {email_cliente}\n"
                    ."*WhatsApp:* {telefone_cliente}\n"
                    ."*Total:* {total_pedido}\n"
                    ."*Status:* {status_pedido}\n\n"
                    ."*Itens:*\n{itens_pedido}\n\n"
                    ."📍 *Retirada em {local_retirada}*\n{instrucoes_retirada}\n\n"
                    ."*Horários:*\n{horarios_retirada}\n\n"
                    ."*Observações:* {observacoes}",
                'enviar_imagem_produto' => true,
                'enviar_localizacao' => true,
                'imagem_url' => '',
            ],
            self::CLIENTE_PEDIDO_REGISTRADO => [
                'mensagem' => "Olá, *{nome_cliente}*! 🙌\n\n"
                    ."Seu pedido *{referencia_pedido}* na loja Convictos foi registrado.\n\n"
                    ."*Total:* {total_pedido}\n\n"
                    ."📍 *Retirada em {local_retirada}*\n{instrucoes_retirada}\n\n"
                    ."*Horários:*\n{horarios_retirada}\n\n"
                    ."Aguardamos a confirmação do pagamento. Qualquer dúvida, estamos à disposição!",
                'enviar_imagem_produto' => true,
                'enviar_localizacao' => true,
                'imagem_url' => '',
            ],
            self::CLIENTE_EM_SEPARACAO => [
                'mensagem' => "Olá, *{nome_cliente}*! 🙌\n\n"
                    ."Atualização do pedido *{referencia_pedido}*\n\n"
                    ."*Status:* {status_anterior} → *{status_pedido}*\n\n"
                    ."{status_mensagem}\n\n"
                    ."📍 *Retirada em {local_retirada}*\n{instrucoes_retirada}\n\n"
                    ."*Horários:*\n{horarios_retirada}",
                'enviar_imagem_produto' => true,
                'enviar_localizacao' => true,
                'imagem_url' => '',
            ],
            self::CLIENTE_PRONTO_RETIRADA => [
                'mensagem' => "Olá, *{nome_cliente}*! 🙌\n\n"
                    ."Seu pedido *{referencia_pedido}* está *pronto para retirada*!\n\n"
                    ."{status_mensagem}\n\n"
                    ."📍 *Retirada em {local_retirada}*\n{instrucoes_retirada}\n\n"
                    ."*Horários:*\n{horarios_retirada}\n\n"
                    ."Apresente esta mensagem ou o comprovante no momento da retirada.",
                'enviar_imagem_produto' => true,
                'imagem_url' => '',
            ],
            self::CLIENTE_RETIRADO => [
                'mensagem' => "Olá, *{nome_cliente}*! 🙌\n\n"
                    ."Confirmamos a retirada do pedido *{referencia_pedido}*.\n\n"
                    ."{status_mensagem}\n\n"
                    ."Obrigado por vestir a convicção! 🔥",
                'enviar_imagem_produto' => false,
                'enviar_localizacao' => false,
                'imagem_url' => '',
            ],
            self::CLIENTE_CANCELADO => [
                'mensagem' => "Olá, *{nome_cliente}*.\n\n"
                    ."O pedido *{referencia_pedido}* foi cancelado.\n\n"
                    ."{status_mensagem}\n\n"
                    ."Em caso de dúvidas, fale conosco.",
                'enviar_imagem_produto' => false,
                'enviar_localizacao' => false,
                'imagem_url' => '',
            ],
            default => [
                'mensagem' => '',
                'enviar_imagem_produto' => false,
                'enviar_localizacao' => false,
                'imagem_url' => '',
            ],
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
        return storage_path('app/loja-notificacao-templates.json');
    }
}
