<?php

return [
    'directory' => 'backups',
    'temporary_directory' => 'backups/tmp',
    'maximum_upload_kilobytes' => 204800,
    'process_timeout_seconds' => 900,
    'maximum_statement_bytes' => 64 * 1024 * 1024,

    /*
    |--------------------------------------------------------------------------
    | MySQL native tools
    |--------------------------------------------------------------------------
    |
    | The manager detects DBngin, MySQL and MariaDB automatically. These
    | values only need to be configured when the binaries live elsewhere.
    |
    */
    'binary_directory' => env('DB_BACKUP_BIN_DIR'),
    'dump_binary' => env('DB_BACKUP_DUMP_BINARY'),
    'client_binary' => env('DB_BACKUP_CLIENT_BINARY'),
];
