<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most of the views for the application are stored in this directory.
    | You may add additional paths if your application uses view namespaces.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | Blade stores compiled templates here. Keep a safe filesystem fallback so
    | a missing or empty VIEW_COMPILED_PATH cannot break the application.
    |
    */

    'compiled' => env('VIEW_COMPILED_PATH') ?: storage_path('framework/views'),

];
