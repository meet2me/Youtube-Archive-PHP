<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\UpdateStats;
use App\Console\Commands\UpdateChannels;
use App\Console\Commands\UpdateVideos;

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

        $this->app->singleton('update:channels', function()
        {
            return new UpdateChannels;
        });

        $this->app->singleton('update:videos', function()
        {
            return new UpdateVideos;
        });

        $this->commands(
            'update:stats',
            'update:channels',
            'update:videos'
        );
    }
}
