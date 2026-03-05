<?php

return [

    'backup' => [
        /*
         * আপনার অ্যাপ্লিকেশনের নাম। ব্যাকআপ মনিটর করার সময় এই নামটি দেখা যাবে।
         */
        'name' => env('APP_NAME', 'Totthobox'),

        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    base_path('storage/framework'),
                    base_path('storage/logs'),
                    base_path('storage/app/backup-temp'),
                    base_path('.git'),
                ],
                'follow_links' => false,
                'ignore_unreadable_directories' => true,
                'relative_path' => null,
            ],

            'databases' => [
                'mysql',
            ],
        ],

        /*
         * ডেটাবেস ডাম্প কম্প্রেসর: এটি ব্যাকআপ ফাইল অনেক ছোট রাখে।
         */
        'database_dump_compressor' => Spatie\DbDumper\Compressors\GzipCompressor::class,

        'database_dump_file_timestamp_format' => 'Y-m-d-H-i-s',

        'database_dump_filename_base' => 'database',

        'database_dump_file_extension' => 'sql',

        'destination' => [
            'compression_method' => ZipArchive::CM_DEFAULT,
            'compression_level' => 9,
            'filename_prefix' => 'totthobox-backup-',
            'disks' => [
                'google',
            ],
            'continue_on_failure' => true,
        ],

        'temporary_directory' => storage_path('app/backup-temp'),

        /*
         * পাসওয়ার্ড এখানে null করে দেওয়া হয়েছে আপনার অনুরোধ অনুযায়ী।
         */
        'password' => null,
        'encryption' => null,

        'tries' => 3,
        'retry_delay' => 60,
    ],

    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => ['mail'],
        ],

        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            // নোটিফিকেশন আপনার পার্সোনাল মেইলে যাবে
            'to' => 'mariamsara.freelancer@gmail.com',

            'from' => [
                // আপনার প্রোজেক্টের মেইল থেকে নোটিফিকেশন পাঠানো হবে
                'address' => 'totthobox@gmail.com',
                'name' => 'Totthobox Backup System',
            ],
        ],
    ],

    'monitor_backups' => [
        [
            'name' => 'Totthobox',
            'disks' => ['google'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 2000,
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days' => 3,
            'keep_daily_backups_for_days' => 14,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 6,
            'keep_yearly_backups_for_years' => 1,
            'delete_oldest_backups_when_using_more_megabytes_than' => 4000,
        ],
    ],
];