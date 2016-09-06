<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\UpdateStats;

class CommandServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('update:stats', function()
        {
            return new UpdateStats;
        });

        $this->commands(
            'update:stats'
        );
    }
}
