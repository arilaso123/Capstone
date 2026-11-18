<?php

$configuredDirectory = env('DATABASE_BACKUP_DIRECTORY');
$isAbsoluteDirectory = $configuredDirectory !== null
    && (str_starts_with($configuredDirectory, '/')
        || str_starts_with($configuredDirectory, '\\')
        || preg_match('/^[A-Za-z]:[\\\\\/]/', $configuredDirectory) === 1);

return [
    'mysqldump' => env('MYSQLDUMP_PATH', 'mysqldump'),
    'mysql' => env('MYSQL_CLIENT_PATH', 'mysql'),
    'directory' => $configuredDirectory
        ? ($isAbsoluteDirectory ? $configuredDirectory : base_path($configuredDirectory))
        : storage_path('app/private/backups'),
    'retention_days' => (int) env('DATABASE_BACKUP_RETENTION_DAYS', 14),
];
