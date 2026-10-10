<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Concurrency Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default concurrency driver used by Laravel's
    | concurrency utilities. Supported drivers: "process", "fork", "sync".
    |
    */

    'default' => env('CONCURRENCY_DRIVER', 'process'),

];
