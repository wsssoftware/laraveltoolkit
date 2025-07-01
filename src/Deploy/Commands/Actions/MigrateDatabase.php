<?php

namespace Laraveltoolkit\Deploy\Commands\Actions;

class MigrateDatabase extends Action
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:migrate';

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
            fn() => $this->callSilent('migrate', ['--force' => true]),
            'Migrating database...',
            'Database migrated successfully!',
            'Database migration failed!'
        );

        return self::SUCCESS;
    }

    public static function shortLabel(): string
    {
        return 'Migrate Database';
    }
}
