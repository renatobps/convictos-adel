<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class NotificacaoPosInscricaoConfig
{
    public static function mensagemPosInscricao(): string
    {
        $data = self::ler();
        $mensagem = (string) ($data['mensagem_pos_inscricao'] ?? $data['mensagem'] ?? '');

        return $mensagem !== '' ? $mensagem : self::mensagemPosInscricaoPadrao();
    }

    public static function salvarMensagemPosInscricao(string $mensagem): void
    {
        $payload = self::ler();
        $payload['mensagem_pos_inscricao'] = trim($mensagem);
        self::escrever($payload);
    }

    public static function mensagemConfirmada(): string
    {
        $data = self::ler();
        $mensagem = (string) ($data['mensagem_confirmada'] ?? '');

        return $mensagem !== '' ? $mensagem : self::mensagemConfirmadaPadrao();
    }

    public static function salvarMensagemConfirmada(string $mensagem): void
    {
        $payload = self::ler();
        $payload['mensagem_confirmada'] = trim($mensagem);
        self::escrever($payload);
    }

    public static function imagemPosInscricaoUrl(): string
    {
        $data = self::ler();
        $url = trim((string) ($data['imagem_pos_inscricao_url'] ?? ''));

        return $url !== '' ? $url : (string) config('services.evolution_api.pos_inscricao_image_url', '');
    }

    public static function salvarImagemPosInscricaoUrl(string $url): void
    {
        $payload = self::ler();
        $payload['imagem_pos_inscricao_url'] = trim($url);
        self::escrever($payload);
    }

    public static function mensagemPosInscricaoPadrao(): string
    {
        return "A paz do Senhor, {nome_do_inscrito}! 🙌\n"
            . "Sua inscrição no *Convictos UM 2027* foi registrada com sucesso!\n\n"
            . "Prepare-se para viver um tempo inesquecível na presença de Deus — *Para que todos sejam um*.\n\n"
            . "Para concluir, procure seu líder de jovens e realize o pagamento do ingresso com camiseta.\n"
            . "Após a confirmação dos dados, você receberá a validação final.\n\n"
            . "📌 Tamanho da camiseta: {tamanho_camiseta}\n\n"
            . "Qualquer dúvida, estamos à disposição!\n"
            . "Nos vemos lá! 🔥";
    }

    public static function mensagemConfirmadaPadrao(): string
    {
        return "*A paz do Senhor, {nome_do_inscrito}! 🙌*\n"
            . "_Seu pagamento foi confirmado com sucesso!_\n\n"
            . "> Sua inscrição no *Convictos UM 2027* está oficialmente validada!\n\n"
            . "📌 *Status:* Confirmada\n"
            . "📌 *Camiseta:* {tamanho_camiseta}\n\n"
            . "Qualquer dúvida, estamos à disposição!\n"
            . "*Nos vemos lá!* 🔥";
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
        return storage_path('app/notificacao-pos-inscricao.json');
    }
}
