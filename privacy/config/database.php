<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql2'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE','u5611458_gui_inventory_sub_laravel'),
            'username' => env('DB_USERNAME','root'),
            'password' => env('DB_PASSWORD',''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        
        'mysql_front_sub' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_FS', '127.0.0.1'),
            'port' => env('DB_PORT_FS', '3306'),
            'database' => env('DB_DATABASE_FS','u5611458_gui_front_sub_laravel'),
            'username' => env('DB_USERNAME_FS','root'),
            'password' => env('DB_PASSWORD_FS',''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        
        'mysqlemkl' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE_6','u5611458_gui_inventory_emkl_laravel'),
            'username' => env('DB_USERNAME_6','root'),
            'password' => env('DB_PASSWORD_6',''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'mysqlskt' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE_2','u5611458_gui_inventory_skt_laravel'),
            'username' => env('DB_USERNAME_2','root'),
            'password' => env('DB_PASSWORD_2',''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'mysqldepo' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE_3','u5611458_gui_inventory_depo_laravel'),
            'username' => env('DB_USERNAME_3','root'),
            'password' => env('DB_PASSWORD_3',''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'mysqlpbm' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE_4','u5611458_gui_inventory_pbm_laravel'),
            'username' => env('DB_USERNAME_4','root'),
            'password' => env('DB_PASSWORD_4',''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        
        'mysqlpbmlama' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE_4L','u5611458_gui_inventory_pbm_laravel_lama'),
            'username' => env('DB_USERNAME_4L','root'),
            'password' => env('DB_PASSWORD_4L',''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        
        'mysqlinfra' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE_INF','u5611458_gui_inventory_pbminfra_laravel'),
            'username' => env('DB_USERNAME_INF','root'),
            'password' => env('DB_PASSWORD_INF',''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        
        'mysql_front_emkl' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE_FEMKL', 'u5611458_gui_front_emkl_laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        
        'mysql_front_pbm' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_FPBM', '127.0.0.1'),
            'port' => env('DB_PORT_FPBM', '3306'),
            'database' => env('DB_DATABASE_FPBM', 'u5611458_gui_front_pbm_laravel'),
            'username' => env('DB_USERNAME_PBM', 'root'),
            'password' => env('DB_PASSWORD_PBM', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        
        'mysql_front_inf' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_FPBM', '127.0.0.1'),
            'port' => env('DB_PORT_FPBM', '3306'),
            'database' => env('DB_DATABASE_FPBM', 'u5611458_gui_front_pbminfra_laravel'),
            'username' => env('DB_USERNAME_PBM', 'root'),
            'password' => env('DB_PASSWORD_PBM', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'mysqlgut' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE_5','u5611458_gui_inventory_gut_laravel'),
            'username' => env('DB_USERNAME_5','root'),
            'password' => env('DB_PASSWORD_5',''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'mysql_finance_emkl' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_EMKL', '127.0.0.1'),
            'port' => env('DB_PORT_EMKL', '3306'),
            'database' => env('DB_DATABASE_EMKL', 'u5611458_gui_finance_emkl_laravel'),
            'username' => env('DB_USERNAME_EMKL', 'root'),
            'password' => env('DB_PASSWORD_EMKL', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        
        'mysql_finance_gut' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_GUT', '127.0.0.1'),
            'port' => env('DB_PORT_GUT', '3306'),
            'database' => env('DB_DATABASE_GUT', 'u5611458_gui_finance_gut_laravel'),
            'username' => env('DB_USERNAME_GUT', 'root'),
            'password' => env('DB_PASSWORD_GUT', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        
        'mysql_finance_gutjkt' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_GUTJKT', '127.0.0.1'),
            'port' => env('DB_PORT_GUTJKT', '3306'),
            'database' => env('DB_DATABASE_GUTJKT', 'u5611458_gui_finance_gutjkt_laravel'),
            'username' => env('DB_USERNAME_GUTJKT', 'root'),
            'password' => env('DB_PASSWORD_GUTJKT', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'mysql_finance_pbm' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_PBM', '127.0.0.1'),
            'port' => env('DB_PORT_PBM', '3306'),
            'database' => env('DB_DATABASE_PBM', 'u5611458_gui_finance_pbm_laravel'),
            'username' => env('DB_USERNAME_PBM', 'root'),
            'password' => env('DB_PASSWORD_PBM', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        
        'mysql_finance_inf' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_PBM', '127.0.0.1'),
            'port' => env('DB_PORT_PBM', '3306'),
            'database' => env('DB_DATABASE_FININF', 'u5611458_gui_finance_pbminfra_laravel'),
            'username' => env('DB_USERNAME_FININF', 'root'),
            'password' => env('DB_PASSWORD_FININF', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'mysql_finance_depo' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_DEPO', '127.0.0.1'),
            'port' => env('DB_PORT_DEPO', '3306'),
            'database' => env('DB_DATABASE_DEPO', 'u5611458_gui_finance_depo_laravel'),
            'username' => env('DB_USERNAME_DEPO', 'root'),
            'password' => env('DB_PASSWORD_DEPO', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'mysql_finance_sub' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_SUB', '127.0.0.1'),
            'port' => env('DB_PORT_SUB', '3306'),
            'database' => env('DB_DATABASE_SUB', 'u5611458_gui_finance_sub_laravel'),
            'username' => env('DB_USERNAME_SUB', 'root'),
            'password' => env('DB_PASSWORD_SUB', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        
        'mysql2' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_SECOND', '127.0.0.1'),
            'port' => env('DB_PORT_SECOND', '3306'),
            'database' => env('DB_DATABASE_SECOND', 'u5611458_db_pusat'),
            'username' => env('DB_USERNAME_SECOND', 'root'),
            'password' => env('DB_PASSWORD_SECOND', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'mysql4' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_FORTH', '127.0.0.1'),
            'port' => env('DB_PORT_FORTH', '3306'),
            'database' => env('DB_DATABASE_FORTH', 'u5611458_gui_general_ledger_laravel'),
            'username' => env('DB_USERNAME_FORTH', 'root'),
            'password' => env('DB_PASSWORD_FORTH', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'mysql7' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_SEVEN', '127.0.0.1'),
            'port' => env('DB_PORT_SEVEN', '3306'),
            'database' => env('DB_DATABASE_SEVEN', 'u5611458_gui_general_ledger_laravel'),
            'username' => env('DB_USERNAME_SEVEN', 'root'),
            'password' => env('DB_PASSWORD_SEVEN', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
