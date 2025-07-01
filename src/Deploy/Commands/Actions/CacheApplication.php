<?php

namespace Laraveltoolkit\Deploy\Commands\Actions;

use function Laravel\Prompts\confirm;

class CacheApplication extends Action
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (self::$fake) {
            return self::SUCCESS;
        }
        $isProduction = app()->isProduction();

        $this->spin(
            fn () => $this->callSilent('cache:clear'),
            'Clearing cache...',
            'Cache cleared successfully!',
            'Cache clear failed!'
        );

        if (
            $isProduction ||
            confirm('This isn\'t a production application, do you want to cache route, config and views?', false)
        ) {
            $this->spin(
                fn () => $this->callSilent('route:cache'),
                'Caching routes...',
                'Routes cached successfully!',
                'Routes cache failed!'
            );

            $this->spin(
                fn () => $this->callSilent('config:cache'),
                'Caching config...',
                'Config cached successfully!',
                'Config cache failed!'
            );
            $this->spin(
                fn () => $this->callSilent('event:cache'),
                'Caching events...',
                'Events cached successfully!',
                'Events cache failed!'
            );
            $this->spin(
                fn () => $this->callSilent('view:cache'),
                'Caching views...',
                'Views cached successfully!',
                'Views cache failed!'
            );
            $this->spin(
                fn () => $this->callSilent('storage:link'),
                'Linking storage...',
                'Storage linked successfully!',
                'Storage link failed!'
            );
        } else {
            $this->spin(
                fn () => $this->callSilent('route:clear'),
                'Clearing routes...',
                'Routes cleared successfully!',
                'Routes clear failed!'
            );
            $this->spin(
                fn () => $this->callSilent('config:clear'),
                'Clearing config...',
                'Config cleared successfully!',
                'Config clear failed!'
            );
            $this->spin(
                fn () => $this->callSilent('event:clear'),
                'Clearing events...',
                'Events cleared successfully!',
                'Events clear failed!'
            );
            $this->spin(
                fn () => $this->callSilent('view:clear'),
                'Clearing views...',
                'Views cleared successfully!',
                'Views clear failed!'
            );
        }

        return self::SUCCESS;
    }

    public static function shortLabel(): string
    {
        return 'Terminate Horizon';
    }
}
