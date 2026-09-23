<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        // @asset('assets/js/x.js') em vez de asset(): junta a versão do ficheiro
        // ao endereço, para o browser não ficar com JavaScript velho depois de
        // uma publicação. Ver App\Support\Asset.
        Blade::directive('asset', fn ($expressao) => "<?php echo \App\Support\Asset::versionado($expressao); ?>");
    }
}
