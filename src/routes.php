<?php

Route::get(Config::get('swagger.doc-http-access')
           . '/{environment?}/{page?}', function ($environment = null, $page = null) {

    if (strpos($environment, ".json") !== false) {
        $page = $environment;
        $environment = null;
    }

    $env = environmentToWorking($environment);

    $filePath = Config::get('swagger.docs-dir') . '/' . strtolower($env['name']) . "/{$page}";

    if (File::extension($filePath) === "") {
        $filePath .= ".json";
    }

    if (!File::Exists($filePath)) {
        App::abort(404, "Cannot find {$filePath}");
    }

    $content = File::get($filePath);

    return Response::make($content, 200, [
        'Content-Type' => 'application/json',
    ]);
});

Route::group(['prefix' => Config::get('swagger.doc-ui-access')], function () {
    $defs = Config::get('swagger.swagger-defs');

    foreach ($defs as $def => $settings) {
        if (key_exists('routes', $settings) && get_class($settings['routes']) === \Closure::class) {
            Route::group(['prefix' => $def], function () use ($settings) {
                $settings['routes']();
            });
        }
    }

    Route::get('{environment?}', function ($environment = null) {
        $env = environmentToWorking($environment);

        $swagger_doc_dir = Config::get('swagger.docs-dir') . '/' . strtolower($env['name']);
        //    return $env;

        if (!File::exists($swagger_doc_dir) || is_writable($swagger_doc_dir)) {
            if (File::exists($swagger_doc_dir)) {
                File::deleteDirectory($swagger_doc_dir);
            }

            recurCreateFolder($swagger_doc_dir);

            $excludeDirs = $env['excludes'] ?? [];
            $appDir = $env['base-annotations-dir'] ?? Config::get('swagger.defaults.base-annotations-dir');

            $swagger = \Swagger\scan($appDir, [
                'exclude' => $excludeDirs,
            ]);

            $filename = $swagger_doc_dir . '/api-docs.json';
            file_put_contents($filename, $swagger);
        }

        $callback = $env['view-renderer'] ?? function ($env) {
                return view('package-swagger::main', [
                    "api_docs" => '/' . Config::get('swagger.doc-http-access') . '/' . strtolower($env['name'])
                                  . '/api-docs.json',
                ]);
            };

        return $callback($env);
    });
});

function recurCreateFolder($path, $split = null) {
    if (is_null($split))
        return recurCreateFolder($path, explode("/", $path));

    $top = array_pop($split);

    $imploded = implode('/', $split);
    if (!File::exists($imploded)) // We have to recur down;
        recurCreateFolder($path, $split);

    if (!is_writable($imploded))
        abort(500, $imploded . ' is not writable');

    File::makeDirectory($imploded . '/' . $top);
}

function environmentToWorking($environment) {
    $default = Config::get('swagger.default-swagger-def');

    $specs = Config::get('swagger.swagger-defs');

    $env = @$specs[$environment ?? $default];

    if (is_null($env))
        abort(404, "Swagger def not found. " . $environment);

    return $env;
}
