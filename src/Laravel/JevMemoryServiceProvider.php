<?php

namespace Jev\Memory\Laravel;

use Illuminate\Support\ServiceProvider;
use Jev\Memory\Contracts\KnowledgeProvider;
use Jev\Memory\Contracts\MeaningResolver;
use Jev\Memory\Contracts\MemoryRetriever;
use Jev\Memory\Contracts\RetrievalPlanner;
use Jev\Memory\Contracts\ThreadRepository;
use Jev\Memory\Contracts\TurnCompactor;
use Jev\Memory\Context\ContextBuilder as ContextContextBuilder;
use Jev\Memory\Support\DefaultRetrievalPlanner;
use Jev\Memory\Support\NullKnowledgeProvider;
use Jev\Memory\Support\NullMemoryRetriever;
use Jev\Memory\Support\RulesMeaningResolver;
use Jev\Memory\Support\ThreadManager;
use Jev\Memory\Support\TurnIngestor;
use Jev\Memory\Support\NullTurnCompactor;
use Jev\Memory\Support\MemoryManager;

class JevMemoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/jev-memory.php', 'jev-memory');

        $this->app->singleton(ThreadRepository::class, ThreadManager::class);
        $this->app->singleton(ThreadManager::class);
        $this->app->singleton(MemoryManager::class);
        $this->app->singleton(TurnIngestor::class);
        $this->app->bind(MeaningResolver::class, RulesMeaningResolver::class);
        $this->app->bind(RetrievalPlanner::class, DefaultRetrievalPlanner::class);
        $this->app->bind(MemoryRetriever::class, NullMemoryRetriever::class);
        $this->app->bind(KnowledgeProvider::class, NullKnowledgeProvider::class);
        $this->app->bind(TurnCompactor::class, NullTurnCompactor::class);
        $this->app->singleton(ContextContextBuilder::class, function ($app) {
            return new ContextContextBuilder(
                $app->make(MeaningResolver::class),
                $app->make(RetrievalPlanner::class),
                $app->make(MemoryRetriever::class),
                $app->make(KnowledgeProvider::class),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/jev-memory.php' => config_path('jev-memory.php'),
        ], 'jev-memory-config');
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'jev-memory-migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
