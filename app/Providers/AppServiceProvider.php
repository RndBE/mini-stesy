<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Chatbot\ToolRegistry::class, function ($app) {
            $r = new \App\Services\Chatbot\ToolRegistry();
            foreach ([
                \App\Services\Chatbot\Tools\ListLoggersTool::class,
                \App\Services\Chatbot\Tools\LoggerDetailTool::class,
                \App\Services\Chatbot\Tools\CompareLoggersTool::class,
                \App\Services\Chatbot\Tools\LoggerHistoryTool::class,
                \App\Services\Chatbot\Tools\LoggerChartTool::class,
                \App\Services\Chatbot\Tools\RainOverviewTool::class,
            ] as $tool) {
                $r->register($app->make($tool));
            }
            return $r;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');

        Blade::if('permission', function (string $permission): bool {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });
    }
}
