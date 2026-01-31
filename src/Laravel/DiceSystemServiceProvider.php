<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Cache\Repository;
use Laragod\DiceSystem\Contract\SeedObserverInterface;
use Laragod\DiceSystem\Contract\SeedProviderInterface;
use Laragod\DiceSystem\Contract\SeedStoreInterface;
use Laragod\DiceSystem\Factory\RollerFactory;
use Laragod\DiceSystem\Seed\PersistentSeedProvider;

class DiceSystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Default persistent store: Laravel cache
        $this->app->singleton(SeedStoreInterface::class, function () {
            /** @var Repository $cache */
            $cache = $this->app->make(Repository::class);
            return new LaravelCacheSeedStore($cache);
        });

        // Persistent seed provider
        $this->app->singleton(SeedProviderInterface::class, function () {
            return new PersistentSeedProvider(
                $this->app->make(SeedStoreInterface::class),
                enabled: true,
            );
        });

        $this->app->singleton(RollerFactory::class, function () {
            $observer = $this->app->bound(SeedObserverInterface::class)
                ? $this->app->make(SeedObserverInterface::class)
                : null;

            return new RollerFactory(
                $this->app->make(SeedProviderInterface::class),
                $observer,
            );
        });
    }
}
