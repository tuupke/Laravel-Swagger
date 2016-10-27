<?php

return [

    /*
      |--------------------------------------------------------------------------
      | Absolute path to location where parsed swagger annotations will be stored
      |--------------------------------------------------------------------------
    */
    'docs-dir'        => storage_path() . '/docs',

    /*
      |--------------------------------------------------------------------------
      | Path to the access location for the docs.
      |--------------------------------------------------------------------------
    */
    'doc-http-access' => 'api-docs',

    'doc-ui-access' => 'docs',

    'default-swagger-def' => 'default',

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

            "includes"          => [],
            "generateOnRequest" => true,

            "api-key"              => "Authorization",
            "swagger-version"      => "2.0",
            "views"                => "",
            "base-annotations-dir" => base_path() . "/app",
            "view-renderer"        => function ($env) {
                return view('package-swagger::main', [
                    "api_docs" => '/' . Config::get('swagger.doc-http-access') . '/' . strtolower($env['name'])
                                  . '/api-docs.json',
                ]);
            },
        ],
    ],

    "defaults" => [
        "swagger-version"      => "2.0",
        "base-path"            => "/",
        "base-annotations-dir" => base_path() . "/app",
        "api-version"          => "1.0.0",
    ],
];
