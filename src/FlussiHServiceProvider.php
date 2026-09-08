<?php

namespace FlussiH\Validator;

use FlussiH\Validator\Console\ValidateFlussiHCommand;
use FlussiH\Validator\Reports\XlsxReportExporter;
use Illuminate\Support\ServiceProvider;

final class FlussiHServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/config/flussi-h.php', 'flussi-h');

        $this->app->singleton('flussi-h-validator', function (): FlussiHValidator {
            return new FlussiHValidator((array) $this->app['config']->get('flussi-h', []));
        });
        $this->app->alias('flussi-h-validator', FlussiHValidator::class);
        $this->app->singleton(XlsxReportExporter::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(dirname(__DIR__).'/resources/views', 'flussi-h-validator');
        if ((bool) $this->app['config']->get('flussi-h.web.enabled', true)) {
            $this->loadRoutesFrom(dirname(__DIR__).'/routes/web.php');
        }

        $this->publishes([
            dirname(__DIR__).'/config/flussi-h.php' => config_path('flussi-h.php'),
        ], 'flussi-h-config');
        $this->publishes([
            dirname(__DIR__).'/resources/views' => resource_path('views/vendor/flussi-h-validator'),
        ], 'flussi-h-views');

        if ($this->app->runningInConsole()) {
            $this->commands([ValidateFlussiHCommand::class]);
        }
    }
}
