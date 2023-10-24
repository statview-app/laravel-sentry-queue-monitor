<?php

namespace Statview\LaravelSentryQueueMonitor;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Sentry\CheckIn;
use Sentry\CheckInStatus;
use Sentry\Event as SentryEvent;
use Sentry\MonitorConfig;
use Sentry\MonitorSchedule;
use Sentry\SentrySdk;

class CheckInJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct()
    {
    }

    public function handle()
    {
        $options = SentrySdk::getCurrentHub()->getClient()->getOptions();

        $slug = 'hack_cron_monitor_for_queue';
        $id = null;

        // Start checkin
        $checkIn = new CheckIn(
            $slug,
            CheckInStatus::inProgress(),
            $id,
            $options->getRelease(),
            $options->getEnvironment(),
        );

        $checkIn->setMonitorConfig(new MonitorConfig(
            MonitorSchedule::crontab('* * * * *'),
            null,
            null,
            'Europe/Amsterdam',
        ));

        $event = SentryEvent::createCheckIn();
        $event->setCheckIn($checkIn);

        SentrySdk::getCurrentHub()->captureEvent($event);

        // Finish checkin
        $checkIn->setStatus(CheckInStatus::ok());

        $event = SentryEvent::createCheckIn();
        $event->setCheckIn($checkIn);

        SentrySdk::getCurrentHub()->captureEvent($event);
    }
}
