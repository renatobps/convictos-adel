<?php

namespace App\Providers;

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
use App\Observers\RegistraAtividadeObserver;
use App\Services\AtividadeLogService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $observer = RegistraAtividadeObserver::class;

        Inscricao::observe($observer);
        Membro::observe($observer);
        Regional::observe($observer);
        Igreja::observe($observer);
        Cargo::observe($observer);
        User::observe($observer);
        MembroAcessoRegional::observe($observer);
        NotificacaoGrupo::observe($observer);
        Enquete::observe($observer);
        ConfiguracaoMensagem::observe($observer);
        Product::observe($observer);
        Order::observe($observer);

        Event::listen(Login::class, function (Login $event): void {
            $user = $event->user;

            if (! $user instanceof User || ! $user->canAccessAdminPanel()) {
                return;
            }

            AtividadeLogService::registrarLogin();
        });
    }
}
