<?php

use Laraveltoolkit\Deploy\Intent;
use Laraveltoolkit\StoredAssets\FilenameStoreType;
use Laraveltoolkit\StoredAssets\StoredAssetModel;

return [

    /*
    |--------------------------------------------------------------------------
    | Deploy config
    |--------------------------------------------------------------------------
    | Extends the default Laravel redirector to handle with Inertia redirects
    | to another domain.
    */
    'deploy' => [
        'domain' => env('APP_DOMAIN', 'localhost'),
        'path' => '/maintenance',
        'bypass_secret' => \Illuminate\Support\Str::password(10, true, true, false),
        'inertia_component' => 'Maintenance',
        'default_redirect' => '/',
        'step1' => [
            Intent::make(\Laraveltoolkit\Deploy\Commands\Actions\GitPull::class, ['--release' => '1.']),
            Intent::make(\Laraveltoolkit\Deploy\Commands\Actions\ComposerUpdate::class),
        ],
        'step2' => [
            Intent::make(\Laraveltoolkit\Deploy\Commands\Actions\NpmUpdateAndBuild::class),
            Intent::make(\Laraveltoolkit\Deploy\Commands\Actions\MigrateDatabase::class),
            Intent::make(\Laraveltoolkit\Deploy\Commands\Actions\SeedDatabase::class),
            Intent::make(\Laraveltoolkit\Deploy\Commands\Actions\CacheApplication::class),
            Intent::make(\Laraveltoolkit\Deploy\Commands\Actions\TerminateHorizon::class),
            Intent::make(\Laraveltoolkit\Deploy\Commands\Actions\Pm2Restart::class),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Flash configurations
    |--------------------------------------------------------------------------
    |
    | This configurations will help to configure flash defaults
    |
    | -> defaults: defaults optional properties from flash.
    */
    'flash' => [
        'defaults' => [
            'closable' => null,
            'life' => null,
            'group' => 'lt-default',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Filepond configurations
    |--------------------------------------------------------------------------
    | This configuration will be responsible by some filepond behaviors.
    |
    | -> disk: Default disk used for store tmp files from filepond.
    | -> root_path: Root path from tmp folder.
    | -> garbage_collector: Garbage collector configs
    |    -> probability: The change of gc run. 0.0 mean 0% and 1.0 mean 100%.
    |    -> upload_life: Number of seconds that gc ignore an uploaded file.
    |    -> maximum_interactions: Max number of files deleted on gc run.
    */
    'filepond' => [
        'disk' => env('LT_FILEPOND_DISK', env('FILESYSTEM_DISK', 'local')),
        'root_path' => env('LT_FILEPOND_ROOT_PATH', 'lt_filepond'),
        'garbage_collector' => [
            'probability' => env('LT_FILEPOND_GC_PROBABILITY', 0.1),
            'upload_life' => env('LT_FILEPOND_GC_UPLOAD_LIFE', 60 * 60 * 24),
            'maximum_interactions' => env('LT_FILEPOND_GC_MAXIMUM_INTERACTIONS', 0.1),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO configurations
    |--------------------------------------------------------------------------
    | This configurations will help to configure the behavior of SEO facade and
    | also its defaults
    |
    | -> friendly_url:
    |    -> dictionary: Replace dictionary for some chars.
    |    -> language: language used in slug. Null use the app default language.
    |    -> separator: char used to replace space.
    | -> propagation: If true, will propagate title, description and canonical for open_graph and twitter card when its
    |    change.
    | -> defaults: Defaults values for SEO when no value was configured on requests.
    */
    'seo' => [
        'friendly_url' => [
            'dictionary' => ['@' => 'em', '&' => 'e'],
            'language' => null,
            'separator' => '-',
        ],
        'propagation' => true,
        'defaults' => [
            'title' => null,
            'description' => null,
            'canonical' => null,
            'robots' => [],
            'robots_txt' => [
                'rules' => [
                    [
                        'user_agent' => '*',
                        'allow' => [], // One path for each allow
                        'disallow' => [''], // // One path for each disallow
                    ],
                ],
                'sitemap' => null, // Null or full url to sitemap
            ],
            'open_graph' => [
                'type' => 'website',
                'title' => null,
                'description' => null,
                'url' => null,
                'image' => [
                    'disk' => null,
                    'path' => null,
                    'alt' => null,
                ],
            ],
            'twitter_card' => [
                'site' => null,
                'creator' => null,
                'title' => null,
                'description' => null,
                'image' => [
                    'disk' => null,
                    'path' => null,
                    'alt' => null,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap configurations
    |--------------------------------------------------------------------------
    | This configurations will help to configure the behavior of Sitemap facade
    |
    | -> cache: If an int, will be the cache time in seconds or false to disable.
    | -> default_routes: If true, Laravel Toolkit will create sitemap routes.
    | -> timeout: If an int will change the max execution time from php. If null
    |    it will use the php.ini default. This is helpful for large sitemaps
    | -> max_file_items: Max amount of items on sitemap without trigger log
    | -> max_file_size: Max sitemap size without trigger log
    */
    'sitemap' => [
        'cache' => 21_600,
        'default_routes' => true,
        'timeout' => null,
        'max_file_items' => 50_000,
        'max_file_size' => 50 * 1024 * 1024,
    ],

    /*
    |--------------------------------------------------------------------------
    | Stored assets configurations
    |--------------------------------------------------------------------------
    | This allow you to change some behavior from stored assets
    |
    | -> disk: Default disk that the file will be stored.
    | -> model: File model. Used to allow a dev extend model.
    | -> path: Base path from stored files on disk.
    | -> filename_store_type: Default stored file filename.
    |    More is useful for a very huge file database. 2 it's acceptable for 50kk
    |    or fewer files. A file with uuid: "7c23b...2f1505" with 2 chars will
    |    be stored on "/assets/7c/05/7c23b...2f1505/..."
    | -> trash_bin: Trash bin configurations
    |    -> folder: Folder of trash bin inside a base path
    |    -> deadline: How many minutes trash will be alive on the folder before
    |       to be definitely deleted.
    */
    'stored_assets' => [
        'disk' => env('LT_STORED_ASSETS_DISK', env('FILESYSTEM_DISK', 'local')),
        'model' => StoredAssetModel::class,
        'path' => env('LT_STORED_ASSETS_PATH', 'assets'),
        'filename_store_type' => FilenameStoreType::UUID,
        'trash_bin_cleaner_timeout' => 55,
        'trash_bin' => [
            'folder' => env('LT_STORED_ASSETS_TRASH_BIN_FOLDER', '$TRASH_BIN'),
            'deadline' => env('LT_STORED_ASSETS_TRASH_BIN_DEADLINE', 60 * 24 * 15),
        ],
    ],
];
