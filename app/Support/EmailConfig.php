<?php

namespace App\Support;

use App\Models\Inscricao;
use App\Mail\InscricaoStatusMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EmailConfig
{
    public const TIPO_REALIZADA = 'realizada';

    public const TIPO_CONFIRMADA = 'confirmada';

    /**
     * @return array<string, mixed>
     */
    public static function smtp(): array
    {
        $data = self::ler();
        $smtp = is_array($data['smtp'] ?? null) ? $data['smtp'] : [];

        return array_merge(self::smtpPadrao(), $smtp);
    }

    /**
     * @param  array<string, mixed>  $smtp
     */
    public static function salvarSmtp(array $smtp): void
    {
        $payload = self::ler();
        $payload['smtp'] = array_merge(self::smtp(), array_filter(
            $smtp,
            static fn ($key) => array_key_exists($key, self::smtpPadrao()),
            ARRAY_FILTER_USE_KEY,
        ));
        self::escrever($payload);
    }

    /**
     * @return array<string, mixed>
     */
    public static function template(string $tipo): array
    {
        return self::lerTemplateBruto($tipo);
    }

    /**
     * Template com imagem no formato do Filament FileUpload.
     *
     * @return array<string, mixed>
     */
    public static function templateParaFormulario(string $tipo): array
    {
        $template = self::lerTemplateBruto($tipo);
        $template['imagem'] = self::imagemParaFormulario($template['imagem'] ?? null);

        return $template;
    }

    /**
     * @return array<string, mixed>
     */
    private static function lerTemplateBruto(string $tipo): array
    {
        $data = self::ler();
        $tpl = is_array($data[$tipo] ?? null) ? $data[$tipo] : [];

        $merged = array_merge(self::templatePadrao($tipo), $tpl);
        $merged['imagem'] = self::normalizarImagem($merged['imagem'] ?? null) ?? '';

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $template
     */
    public static function salvarTemplate(string $tipo, array $template): void
    {
        $payload = self::ler();
        $imagem = self::normalizarImagem($template['imagem'] ?? null);
        $imagemAtual = self::normalizarImagem(self::lerTemplateBruto($tipo)['imagem'] ?? null);

        if ($imagem !== null && self::isCaminhoTemporario($imagem)) {
            $imagem = $imagemAtual;
        }

        $template['imagem'] = $imagem ?? '';
        $payload[$tipo] = array_merge(self::lerTemplateBruto($tipo), array_filter(
            $template,
            static fn ($key) => array_key_exists($key, self::templatePadrao($tipo)),
            ARRAY_FILTER_USE_KEY,
        ));
        self::escrever($payload);
    }

    /**
     * Formato esperado pelo Filament FileUpload (array associativo).
     *
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

    public static function templateAtivo(string $tipo): bool
    {
        return (bool) (self::template($tipo)['ativo'] ?? false);
    }

    /**
     * @return array<string, string>
     */
    public static function templateOptions(): array
    {
        return [
            self::TIPO_REALIZADA => 'Inscrição realizada',
            self::TIPO_CONFIRMADA => 'Inscrição confirmada',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function templateOptionsAtivos(): array
    {
        return collect(self::templateOptions())
            ->filter(fn (string $label, string $tipo): bool => self::templateAtivo($tipo))
            ->all();
    }

    /**
     * @return array{ok: bool, mensagem: ?string}
     */
    public static function enviarParaInscricao(Inscricao $inscricao, string $tipo): array
    {
        if (! array_key_exists($tipo, self::templateOptions())) {
            return ['ok' => false, 'mensagem' => 'Template de e-mail inválido.'];
        }

        if (! self::templateAtivo($tipo)) {
            return ['ok' => false, 'mensagem' => 'Este template de e-mail está desativado na configuração.'];
        }

        if (blank($inscricao->email) || str_contains((string) $inscricao->email, '@convictos.local')) {
            return ['ok' => false, 'mensagem' => 'Inscrito sem e-mail válido.'];
        }

        try {
            self::aplicarMailer();
            Mail::to($inscricao->email)->send(new InscricaoStatusMail($inscricao, $tipo));

            return ['ok' => true, 'mensagem' => null];
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar e-mail para inscrito', [
                'inscricao_id' => $inscricao->id,
                'tipo' => $tipo,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'mensagem' => 'Falha ao enviar: '.$e->getMessage()];
        }
    }

    public static function emailValidoParaEnvio(?string $email): bool
    {
        return filled($email) && ! str_contains((string) $email, '@convictos.local');
    }

    /**
     * Reenvia o comprovante (e-mail de inscrição realizada, com PDF em anexo),
     * ignorando o toggle de ativo — usado em ações manuais do painel.
     *
     * @return array{ok: bool, mensagem: ?string}
     */
    public static function enviarComprovante(Inscricao $inscricao): array
    {
        if (! self::emailValidoParaEnvio($inscricao->email)) {
            return ['ok' => false, 'mensagem' => 'Inscrito sem e-mail válido.'];
        }

        try {
            self::aplicarMailer();
            Mail::to($inscricao->email)->send(new InscricaoStatusMail($inscricao, self::TIPO_REALIZADA));

            return ['ok' => true, 'mensagem' => null];
        } catch (\Throwable $e) {
            Log::error('Falha ao reenviar comprovante por e-mail', [
                'inscricao_id' => $inscricao->id,
                'message' => $e->getMessage(),
            ]);

            return ['ok' => false, 'mensagem' => 'Falha ao enviar: '.$e->getMessage()];
        }
    }

    public static function imagemUrl(string $tipo): string
    {
        $imagem = self::normalizarImagem(self::lerTemplateBruto($tipo)['imagem'] ?? null);

        if (blank($imagem)) {
            return '';
        }

        if (str_starts_with($imagem, 'http://') || str_starts_with($imagem, 'https://')) {
            return $imagem;
        }

        return url(Storage::disk('public')->url($imagem));
    }

    /**
     * Aplica as credenciais SMTP salvas em runtime (sobrescreve config/mail.php).
     */
    public static function aplicarMailer(): void
    {
        $smtp = self::smtp();
        $mailer = (string) ($smtp['mailer'] ?? 'log');

        Config::set('mail.default', $mailer);

        if ($mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host', $smtp['host'] ?? '');
            Config::set('mail.mailers.smtp.port', (int) ($smtp['port'] ?? 587));
            Config::set('mail.mailers.smtp.username', $smtp['username'] ?? '');
            Config::set('mail.mailers.smtp.password', $smtp['password'] ?? '');
            Config::set('mail.mailers.smtp.scheme', self::esquemaTransporte((string) ($smtp['encryption'] ?? 'tls')));
        }

        if (filled($smtp['from_address'] ?? null)) {
            Config::set('mail.from.address', $smtp['from_address']);
        }

        if (filled($smtp['from_name'] ?? null)) {
            Config::set('mail.from.name', $smtp['from_name']);
        }
    }

    public static function substituirPlaceholders(string $texto, Inscricao $inscricao): string
    {
        $status = Inscricao::statusOptions()[$inscricao->status] ?? (string) $inscricao->status;

        return strtr($texto, [
            '{nome_do_inscrito}' => (string) $inscricao->nome,
            '{tamanho_camiseta}' => (string) $inscricao->tamanho_camiseta,
            '{igreja}' => (string) $inscricao->igreja,
            '{email}' => (string) $inscricao->email,
            '{status}' => $status,
            '{codigo}' => (string) $inscricao->codigo,
            '{link_ingresso}' => filled($inscricao->codigo) ? $inscricao->urlIngresso() : '',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function smtpPadrao(): array
    {
        return [
            'mailer' => (string) config('mail.default', 'log'),
            'host' => (string) config('mail.mailers.smtp.host', ''),
            'port' => (int) config('mail.mailers.smtp.port', 587),
            'username' => (string) config('mail.mailers.smtp.username', ''),
            'password' => (string) config('mail.mailers.smtp.password', ''),
            'encryption' => 'tls',
            'from_address' => (string) config('mail.from.address', ''),
            'from_name' => (string) config('mail.from.name', config('app.name')),
        ];
    }

    private static function esquemaTransporte(string $encryption): ?string
    {
        return match (strtolower($encryption)) {
            'ssl' => 'smtps',
            'tls' => 'smtp',
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function templatePadrao(string $tipo): array
    {
        if ($tipo === self::TIPO_CONFIRMADA) {
            return [
                'ativo' => true,
                'assunto' => 'Inscrição confirmada — Convictos UM 2027',
                'conteudo' => "<p>A paz do Senhor, <strong>{nome_do_inscrito}</strong>! 🙌</p>"
                    . "<p>Seu pagamento foi confirmado e sua inscrição no <strong>Convictos UM 2027</strong> está oficialmente validada!</p>"
                    . "<p><strong>Status:</strong> {status}<br><strong>Camiseta:</strong> {tamanho_camiseta}</p>"
                    . "<p>Prepare-se para viver um tempo inesquecível na presença de Deus. Nos vemos lá! 🔥</p>",
                'imagem' => null,
                'botao_texto' => 'Acessar o site',
                'botao_url' => (string) config('app.url'),
            ];
        }

        return [
            'ativo' => true,
            'assunto' => 'Recebemos sua inscrição — Convictos UM 2027',
            'conteudo' => "<p>A paz do Senhor, <strong>{nome_do_inscrito}</strong>! 🙌</p>"
                . "<p>Sua inscrição no <strong>Convictos UM 2027</strong> foi registrada com sucesso!</p>"
                . "<p>Para concluir, procure seu líder de jovens e realize o pagamento do ingresso com camiseta. Após a confirmação dos dados, você receberá a validação final.</p>"
                . "<p><strong>Tamanho da camiseta:</strong> {tamanho_camiseta}</p>"
                . "<p>Qualquer dúvida, estamos à disposição. Nos vemos lá! 🔥</p>",
            'imagem' => null,
            'botao_texto' => 'Acessar o site',
            'botao_url' => (string) config('app.url'),
        ];
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
        return storage_path('app/email-config.json');
    }
}
