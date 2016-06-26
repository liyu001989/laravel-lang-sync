<?php

namespace Liyu\LaravelLangSync;

use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app['liyu.lang.sync'] = $this->app->share(function () {
            return new LangSyncCommand();
        });
    }

    public function boot()
    {
        $this->commands('liyu.lang.sync');
    }
}
