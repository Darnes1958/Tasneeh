<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [



        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'trust_server_certificate' => true,
        ],
        'other' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL_OTHER'),
            'host' => env('DB_HOST_OTHER', 'localhost'),
            'port' => env('DB_PORT_OTHER', '1433'),
            'database' => env('DB_DATABASE_OTHER', 'forge'),
            'username' => env('DB_USERNAME_OTHER', 'forge'),
            'password' => env('DB_PASSWORD_OTHER', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'trust_server_certificate' => true,
        ],
        'Bokreah' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL_OTHER'),
            'host' => env('DB_HOST_OTHER', 'localhost'),
            'port' => env('DB_PORT_OTHER', '1433'),
            'database' => env('DB_DATABASE_Bokreah', 'forge'),
            'username' => env('DB_USERNAME_OTHER', 'forge'),
            'password' => env('DB_PASSWORD_OTHER', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'trust_server_certificate' => true,
        ],
        'Mehmar' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL_OTHER'),
            'host' => env('DB_HOST_OTHER', 'localhost'),
            'port' => env('DB_PORT_OTHER', '1433'),
            'database' => env('DB_DATABASE_Mehmar', 'forge'),
            'username' => env('DB_USERNAME_OTHER', 'forge'),
            'password' => env('DB_PASSWORD_OTHER', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'trust_server_certificate' => true,
        ],

        'BokreahAcc' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL_OTHER'),
            'host' => env('DB_HOST_OTHER', 'localhost'),
            'port' => env('DB_PORT_OTHER', '1433'),
            'database' => env('DB_DATABASE_BokreahAcc', 'forge'),
            'username' => env('DB_USERNAME_OTHER', 'forge'),
            'password' => env('DB_PASSWORD_OTHER', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'trust_server_certificate' => true,
        ],
        'Bokreah2' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL_OTHER'),
            'host' => env('DB_HOST_OTHER', 'localhost'),
            'port' => env('DB_PORT_OTHER', '1433'),
            'database' => env('DB_DATABASE_Bokreah2', 'forge'),
            'username' => env('DB_USERNAME_OTHER', 'forge'),
            'password' => env('DB_PASSWORD_OTHER', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'trust_server_certificate' => true,
        ],
        'Bokreah2Acc' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL_OTHER'),
            'host' => env('DB_HOST_OTHER', 'localhost'),
            'port' => env('DB_PORT_OTHER', '1433'),
            'database' => env('DB_DATABASE_BokreahAcc2', 'forge'),
            'username' => env('DB_USERNAME_OTHER', 'forge'),
            'password' => env('DB_PASSWORD_OTHER', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'trust_server_certificate' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
