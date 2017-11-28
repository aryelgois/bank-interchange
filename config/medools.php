<?php
/**
 * Example of Configurations for aryelgois\Medools
 *
 * You only need to repeat the 'databases' keys in your application
 */

return [
    'databases' => [
        'default'       => 'bank_interchange',
        'address'       => 'address',
    ],
    'options' => [
        // required
        'database_type' => 'mysql',
        'server'        => 'localhost',
        'username'      => 'root',
        'password'      => 'password',

        // [optional]
        'charset' => 'utf8',
    ]
];
