<?php

namespace App\Providers;

use App\Services\CircuitoBaseDeDados;
use App\Services\DatabaseFallbackCache;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Um por requisição: o disjuntor tem de ser o mesmo para toda a gente
        // que o consulte dentro do mesmo pedido.
        $this->app->singleton(CircuitoBaseDeDados::class, fn () => new CircuitoBaseDeDados());

        $this->app->singleton(
            DatabaseFallbackCache::class,
            fn ($app) => new DatabaseFallbackCache($app->make(CircuitoBaseDeDados::class))
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Directamente na classe, e não pela fachada Schema::. A fachada
        // resolve o construtor de esquema, e isso **estabelece uma ligação ao
        // MySQL em cada requisição** — mesmo numa página sem consulta nenhuma,
        // mesmo num 404. Num alojamento com 30 ligações, era uma ligação
        // gasta por visita para pôr um número numa propriedade estática.
        SchemaBuilder::defaultStringLength(191);
        Paginator::useBootstrap();

        // @asset('assets/js/x.js') em vez de asset(): junta a versão do ficheiro
        // ao endereço, para o browser não ficar com JavaScript velho depois de
        // uma publicação. Ver App\Support\Asset.
        Blade::directive('asset', fn ($expressao) => "<?php echo \App\Support\Asset::versionado($expressao); ?>");
    }
}
