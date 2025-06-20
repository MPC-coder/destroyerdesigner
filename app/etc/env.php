<?php
return [
    'backend' => [
        'frontName' => 'admin_1vvrz1'
    ],
    'remote_storage' => [
        'driver' => 'file'
    ],
    'cron' => [
        'log' => [
            'enabled' => false
        ]
    ],
    'log' => [
        'active' => true,
        'level' => 100
    ],
    'cache' => [
        'graphql' => [
            'id_salt' => 'x4Kd9YKeC9U8cqvxTq9s0M6gOid5qtqC'
        ],
        'frontend' => [
            'default' => [
                'id_prefix' => '8dd_'
            ],
            'page_cache' => [
                'id_prefix' => '8dd_'
            ],
            'customer_data' => [
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => '127.0.0.1',
                    'port' => '6379',
                    'database' => '2',
                    'compress_data' => '1',
                    'read_timeout' => 10,
                    'connect_retries' => 3
                ]
            ]
        ],
        'allow_parallel_generation' => true
    ],
    'config' => [
        'async' => 0
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'crypt' => [
        'key' => 'base6440rO0NX+7DvQAdQ2nm2Xms5g0xtCY1cylRMtIRScxiE='
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => '127.0.0.1',
                'dbname' => 'monp_radicalv23',
                'username' => 'monp_radicaluser',
                'password' => 'x#D!bp@p!aaj',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'persistent' => '1',
                'driver_options' => [
                    1014 => false,
                    12 => true
                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'session' => [
        'save' => 'redis',
        'validate_on_start' => false,
        'redis' => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'password' => '',
            'timeout' => '2.5',
            'persistent_identifier' => '',
            'database' => '2',
            'compression_threshold' => '2048',
            'compression_library' => 'gzip',
            'log_level' => '4',
            'max_concurrency' => '50',
            'break_after_frontend' => '10',
            'break_after_adminhtml' => '30',
            'first_lifetime' => '600',
            'bot_first_lifetime' => '60',
            'bot_lifetime' => '7200',
            'disable_locking' => '0',
            'min_lifetime' => '60',
            'max_lifetime' => '1000000'
        ]
    ],
    'lock' => [
        'provider' => 'db'
    ],
    'directories' => [
        'document_root_is_pub' => true
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'graphql_query_resolver_result' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1
    ],
    'downloadable_domains' => [
        'www.mon-porte-clef.fr'
    ],
    'install' => [
        'date' => 'Tue, 02 Jul 2024 16:06:33 +0000'
    ]
];
