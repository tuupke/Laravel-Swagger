<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Absolute path to location where parsed swagger annotations will be stored
     |--------------------------------------------------------------------------
    */
    'docs-dir' => storage_path() . '/docs',

    /*
     |--------------------------------------------------------------------------
     | Path to the access location for the docs.
     |--------------------------------------------------------------------------
    */
    'doc-http-access' => 'api-docs',

    'doc-ui-access' => 'docs',

    'default-swagger-def' => 'default',

    "always-generate" => true,

    'swagger-defs' => [
        "default" => [
            "name"     => "Default",
            "excludes" => [
                storage_path(),
                base_path() . "/tests",
                base_path() . "/resources/views",
                base_path() . "/config",
                base_path() . "/vendor",
                base_path() . "/public",
                base_path() . "/bootstrap",
                base_path() . "/database",
            ],

            "includes" => [],

            "api-key"              => "Authorization",
            "swagger-version"      => "2.0",
            "views"                => "",
            "base-annotations-dir" => base_path() . "/app",
        ],
    ],

    "defaults" => [
        "swagger-version"      => "3.0",
        "base-path"            => "/",
        "base-annotations-dir" => base_path() . "/app",
        "api-version"          => "1.0.0",
    ],
];
