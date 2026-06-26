<?php

namespace App\Services;

use App\Models\AtividadeLog;
use App\Models\Cargo;
use App\Models\ConfiguracaoMensagem;
use App\Models\Enquete;
use App\Models\Igreja;
use App\Models\Inscricao;
use App\Models\Membro;
use App\Models\MembroAcessoRegional;
use App\Models\NotificacaoGrupo;
use App\Models\Order;
use App\Models\Product;
use App\Models\Regional;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AtividadeLogService
{
    public const ACAO_CRIADO = 'criado';

    public const ACAO_ATUALIZADO = 'atualizado';

    public const ACAO_EXCLUIDO = 'excluido';

    public const ACAO_LOGIN = 'login';

    public const ACAO_CONFIG = 'config';

    public const ACAO_NOTIFICACAO = 'notificacao';

    /** @var array<class-string<Model>, string> */
    private const ROTULOS = [
        Inscricao::class => 'Inscrição',
        Membro::class => 'Membro',
        Regional::class => 'Regional',
        Igreja::class => 'Igreja',
        Cargo::class => 'Cargo',
        User::class => 'Usuário',
        MembroAcessoRegional::class => 'Acesso regional',
        NotificacaoGrupo::class => 'Grupo de notificação',
        Enquete::class => 'Enquete',
        ConfiguracaoMensagem::class => 'Template de mensagem',
        Product::class => 'Produto',
        Order::class => 'Pedido',
    ];

    /** @var array<string, string> */
    private const CAMPOS = [
        'nome' => 'Nome',
        'name' => 'Nome',
        'email' => 'E-mail',
        'status' => 'Status',
        'codigo' => 'Código',
        'tamanho_camiseta' => 'Camiseta',
        'camiseta_retirada' => 'Retirada de camiseta',
        'camiseta_retirada_por' => 'Retirado por',
        'igreja_id' => 'Igreja',
        'regional_id' => 'Regional',
        'cargo_id' => 'Cargo',
        'is_admin' => 'Administrador',
        'titulo' => 'Título',
        'ativo' => 'Ativo',
        'valor' => 'Valor',
        'preco' => 'Preço',
        'whatsapp' => 'WhatsApp',
        'observacoes' => 'Observações',
    ];

    /** @var list<string> */
    private const CAMPOS_IGNORADOS = [
        'password',
        'senha',
        'remember_token',
        'updated_at',
        'created_at',
        'camiseta_retirada_em',
    ];

    public static function registrar(
        string $descricao,
        ?string $acao = null,
        ?Model $entidade = null,
        ?array $detalhes = null,
    ): void {
        if (! self::deveRegistrar()) {
            return;
        }

        $user = Auth::user();

        AtividadeLog::query()->create([
            'user_id' => $user?->id,
            'usuario_nome' => $user?->name ?? 'Sistema',
            'usuario_email' => $user?->email,
            'descricao' => $descricao,
            'acao' => $acao,
            'entidade_tipo' => $entidade ? $entidade::class : null,
            'entidade_id' => $entidade?->getKey(),
            'detalhes' => self::normalizarDetalhes($detalhes),
        ]);
    }

    public static function registrarLogin(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        AtividadeLog::query()->create([
            'user_id' => $user->id,
            'usuario_nome' => $user->name,
            'usuario_email' => $user->email,
            'descricao' => 'Entrou no painel administrativo',
            'acao' => self::ACAO_LOGIN,
        ]);
    }

    public static function registrarCriacao(Model $model): void
    {
        self::registrar(
            'Criou '.self::rotuloEntidade($model).': '.self::identificador($model),
            self::ACAO_CRIADO,
            $model,
        );
    }

    public static function registrarAtualizacao(Model $model): void
    {
        $alteracoes = self::descreverAlteracoes($model);

        if ($alteracoes === '') {
            return;
        }

        self::registrar(
            'Atualizou '.self::rotuloEntidade($model).' '.self::identificador($model).' — '.$alteracoes,
            self::ACAO_ATUALIZADO,
            $model,
            ['alteracoes' => self::serializarAlteracoes($model->getChanges())],
        );
    }

    public static function registrarExclusao(Model $model): void
    {
        self::registrar(
            'Excluiu '.self::rotuloEntidade($model).': '.self::identificador($model),
            self::ACAO_EXCLUIDO,
            $model,
        );
    }

    public static function registrarCriacaoAcessoRegional(MembroAcessoRegional $acesso): void
    {
        $acesso->loadMissing(['membro', 'regional']);

        $membro = $acesso->membro?->nome ?? 'membro #'.$acesso->membro_id;
        $regional = $acesso->regional?->nome ?? 'regional #'.$acesso->regional_id;

        self::registrar(
            "Atribuiu acesso à regional {$regional} para {$membro}",
            self::ACAO_CRIADO,
            $acesso,
        );
    }

    public static function registrarExclusaoAcessoRegional(MembroAcessoRegional $acesso): void
    {
        $acesso->loadMissing(['membro', 'regional']);

        $membro = $acesso->membro?->nome ?? 'membro #'.$acesso->membro_id;
        $regional = $acesso->regional?->nome ?? 'regional #'.$acesso->regional_id;

        self::registrar(
            "Removeu acesso à regional {$regional} de {$membro}",
            self::ACAO_EXCLUIDO,
            $acesso,
        );
    }

    private static function deveRegistrar(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return false;
        }

        // Livewire usa prefixo com hash (ex.: livewire-a0632efa/update), não livewire/update.
        return request()->is('admin', 'admin/*') || str_contains(request()->path(), 'livewire');
    }

    private static function rotuloEntidade(Model $model): string
    {
        return self::ROTULOS[$model::class] ?? class_basename($model);
    }

    private static function identificador(Model $model): string
    {
        if ($model instanceof Inscricao) {
            return trim($model->nome.' ('.$model->codigo.')');
        }

        if ($model instanceof MembroAcessoRegional) {
            $model->loadMissing(['membro', 'regional']);

            return trim(($model->membro?->nome ?? '#'.$model->membro_id).' — '.($model->regional?->nome ?? '#'.$model->regional_id));
        }

        foreach (['nome', 'name', 'titulo', 'codigo', 'email'] as $campo) {
            $valor = $model->getAttribute($campo);
            if (filled($valor)) {
                return (string) $valor;
            }
        }

        return '#'.$model->getKey();
    }

    private static function descreverAlteracoes(Model $model): string
    {
        $dirty = collect($model->getChanges())->except(self::CAMPOS_IGNORADOS);

        if ($dirty->has('password') || $dirty->has('senha')) {
            $dirty = $dirty->except(['password', 'senha']);
            $partes = ['Senha alterada'];
        } else {
            $partes = [];
        }

        foreach ($dirty as $campo => $valor) {
            $label = self::CAMPOS[$campo] ?? $campo;
            $anterior = self::formatarValorCampo($model, $campo, $model->getOriginal($campo));
            $novo = self::formatarValorCampo($model, $campo, $valor);
            $partes[] = "{$label}: {$anterior} → {$novo}";
        }

        return implode('; ', $partes);
    }

    private static function formatarValor(mixed $valor): string
    {
        if ($valor instanceof \DateTimeInterface) {
            return $valor->format('d/m/Y H:i');
        }

        if (is_bool($valor)) {
            return $valor ? 'Sim' : 'Não';
        }

        if ($valor === null || $valor === '') {
            return '—';
        }

        return (string) $valor;
    }

    private static function formatarValorCampo(Model $model, string $campo, mixed $valor): string
    {
        if ($model instanceof Inscricao && $campo === 'status') {
            $rotulos = Inscricao::statusOptions();

            return $rotulos[(string) $valor] ?? self::formatarValor($valor);
        }

        return self::formatarValor($valor);
    }

    /**
     * @param  array<string, mixed>|null  $detalhes
     * @return array<string, mixed>|null
     */
    private static function normalizarDetalhes(?array $detalhes): ?array
    {
        if ($detalhes === null) {
            return null;
        }

        return collect($detalhes)
            ->map(fn (mixed $valor) => is_array($valor) ? self::serializarAlteracoes($valor) : self::formatarValor($valor))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $alteracoes
     * @return array<string, mixed>
     */
    private static function serializarAlteracoes(array $alteracoes): array
    {
        return collect($alteracoes)
            ->map(fn (mixed $valor) => self::formatarValor($valor))
            ->all();
    }
}
