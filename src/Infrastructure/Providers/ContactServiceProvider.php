<?php

declare(strict_types=1);

namespace Infrastructure\Providers;

use Application\Contracts\EventDispatcherInterface;
use Application\Contracts\ScoreProcessingQueueInterface;
use Application\UseCases\Contact\ProcessContactScoreUseCase;
use Application\UseCases\Contact\TriggerScoreProcessingUseCase;
use Domain\Contact\Repositories\ContactRepositoryInterface;
use Domain\Contact\Services\ScoreCalculatorService;
use Domain\Contact\Services\ScoreCalculatorServiceInterface;
use Domain\Contact\Services\ScoreStrategies\EmailScoreStrategy;
use Domain\Contact\Services\ScoreStrategies\NameScoreStrategy;
use Domain\Contact\Services\ScoreStrategies\PhoneScoreStrategy;
use Infrastructure\Events\LaravelEventDispatcher;
use Infrastructure\Persistence\Eloquent\Repositories\EloquentContactRepository;
use Infrastructure\Queue\LaravelScoreProcessingQueue;
use Illuminate\Support\ServiceProvider;

final class ContactServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository
        $this->app->bind(ContactRepositoryInterface::class, EloquentContactRepository::class);

        // Application Contracts → Infrastructure Implementations
        $this->app->bind(EventDispatcherInterface::class, LaravelEventDispatcher::class);
        $this->app->bind(ScoreProcessingQueueInterface::class, LaravelScoreProcessingQueue::class);

        // Score Calculator with all strategies (extensible via Strategy Pattern)
        $this->app->singleton(ScoreCalculatorServiceInterface::class, function () {
            return new ScoreCalculatorService(
                new EmailScoreStrategy(),
                new NameScoreStrategy(),
                new PhoneScoreStrategy(),
            );
        });

        // Use Cases with multiple or non-obvious dependencies require explicit construction
        $this->app->bind(ProcessContactScoreUseCase::class, fn ($app) => new ProcessContactScoreUseCase(
            $app->make(ContactRepositoryInterface::class),
            $app->make(ScoreCalculatorServiceInterface::class),
            $app->make(EventDispatcherInterface::class),
        ));

        $this->app->bind(TriggerScoreProcessingUseCase::class, fn ($app) => new TriggerScoreProcessingUseCase(
            $app->make(ContactRepositoryInterface::class),
            $app->make(ScoreProcessingQueueInterface::class),
        ));
    }

    public function boot(): void
    {
    }
}
