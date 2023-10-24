<?php

namespace Statview\LaravelSentryQueueMonitor;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class MonitorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->app->afterResolving(Schedule::class, static function (Schedule $schedule) {
                $schedule
                    ->call(function () {
                        dispatch(new CheckInJob());
                    })
                    ->everyMinute();
            });
        }
    }
}