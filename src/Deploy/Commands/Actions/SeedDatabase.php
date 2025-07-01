<?php

namespace Laraveltoolkit\Deploy\Commands\Actions;

class SeedDatabase extends Action
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:seed';

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

        $this->spin(
            fn() => $this->callSilent('db:seed', ['--force' => true]),
            'Seeding database...',
            'Database seeded successfully!',
            'Database seed failed!'
        );

        return self::SUCCESS;
    }

    public static function shortLabel(): string
    {
        return 'Seed Database';
    }
}
