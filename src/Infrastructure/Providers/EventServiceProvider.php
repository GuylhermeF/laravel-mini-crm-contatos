<?php

declare(strict_types=1);

namespace Infrastructure\Providers;

use Domain\Contact\Events\ContactScoreProcessed;
use Infrastructure\Listeners\ContactScoreProcessedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

final class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ContactScoreProcessed::class => [
            ContactScoreProcessedListener::class,
        ],
    ];
}
