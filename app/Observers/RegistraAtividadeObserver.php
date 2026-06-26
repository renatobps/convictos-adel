<?php

namespace App\Observers;

use App\Models\MembroAcessoRegional;
use App\Services\AtividadeLogService;
use Illuminate\Database\Eloquent\Model;

class RegistraAtividadeObserver
{
    public function created(Model $model): void
    {
        if ($model instanceof MembroAcessoRegional) {
            AtividadeLogService::registrarCriacaoAcessoRegional($model);

            return;
        }

        AtividadeLogService::registrarCriacao($model);
    }

    public function updated(Model $model): void
    {
        if ($model->wasChanged()) {
            AtividadeLogService::registrarAtualizacao($model);
        }
    }

    public function deleted(Model $model): void
    {
        if ($model instanceof MembroAcessoRegional) {
            AtividadeLogService::registrarExclusaoAcessoRegional($model);

            return;
        }

        AtividadeLogService::registrarExclusao($model);
    }
}
