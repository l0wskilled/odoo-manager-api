<?php

return [
    'database' => [
        'adapter' => 'Mysql', /* Possible Values: Mysql, Postgres, Sqlite */
        'host' => '127.0.0.1',
        'username' => 'manager',
        'password' => 'wurstbrotesindlecker',
        'dbname' => 'odoo_manager',
        'charset' => 'utf8',
    ],
    'log_database' => [
        'adapter' => 'Mysql', /* Possible Values: Mysql, Postgres, Sqlite */
        'host' => '127.0.0.1',
        'username' => 'manager',
        'password' => 'wurstbrotesindlecker',
        'dbname' => 'odoo_manager_logger',
        'charset' => 'utf8',
    ],
    'authentication' => [
        'secret' => 'FingerInPoMexiko', // This will sign the token. (still insecure)
        'encryption_key' => 'Baby, I compare you to a kiss from a rose on the grey', // Secure token with an ultra password
        'expiration_time' => 86400 * 7, // One week till token expires
        'iss' => "odoo-manager", // Token issuer eg. www.myproject.com
        'aud' => "odoo-manager", // Token audience eg. www.myproject.com
    ],
];
