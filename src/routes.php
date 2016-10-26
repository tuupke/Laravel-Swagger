<?php

Route::get(Config::get('swagger.doc-http-access') . '/{environment?}/{page?}', function ($environment = null, $page = null) {

    if(strpos($environment, ".json") !== false){
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
    return Response::make($content, 200, array(
        'Content-Type' => 'application/json'
    ));
});

Route::get(Config::get('swagger.doc-ui-access') . '/{environment?}', function ($environment = null) {
    $env = environmentToWorking($environment);

    $swagger_doc_dir = Config::get('swagger.docs-dir') . '/' . strtolower($env['name']);
    //    return $env;

    if (!File::exists($swagger_doc_dir) || is_writable($swagger_doc_dir)) {
        if (File::exists($swagger_doc_dir)) {
            File::deleteDirectory($swagger_doc_dir);
        }

        recurCreateFolder($swagger_doc_dir);

        //        $defaultBasePath = @$env['base-path'] ?? Config::get('swagger.defaults.base-path');
        //        $defaultApiVersion = @$env['api-version'] ?? Config::get('swagger.defaults.api-version');
        //        $defaultSwaggerVersion = $env['swagger-version'] ?? Config::get('swagger.defaults.swagger-version');
        $excludeDirs = $env['excludes'] ?? [];
        $appDir = $env['base-annotations-dir'] ?? Config::get('swagger.defaults.base-annotations-dir');

        $swagger = \Swagger\scan($appDir, [
            'exclude' => $excludeDirs,
        ]);

        $filename = $swagger_doc_dir . '/api-docs.json';
        file_put_contents($filename, $swagger);
    }

    return view('package-swagger::main', [
        "api_docs" => '/' . Config::get('swagger.doc-http-access') . '/' . strtolower($env['name']) . '/api-docs.json'
    ]);

    return $swagger_doc_dir;

    if (!File::exists($docDir) || is_writable($docDir)) {
        // delete all existing documentation
        if (File::exists($docDir)) {
            File::deleteDirectory($docDir);
        }

        File::makeDirectory($docDir);

        $defaultBasePath = Config::get('swaggervel.default-base-path');
        $defaultApiVersion = Config::get('swaggervel.default-api-version');
        $defaultSwaggerVersion = Config::get('swaggervel.default-swagger-version');
        $excludeDirs = Config::get('swaggervel.excludes');

        $swagger = \Swagger\scan($appDir, [
            'exclude' => $excludeDirs,
        ]);

        $filename = $docDir . '/api-docs.json';
        file_put_contents($filename, $swagger);
    }

    return "Hoi-docs " . $environment;
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
        abort(404, "Swagger def not found.");

    return $env;
}


/*
Route::any(Config::get('swaggervel.doc-route').'/{page?}', function($page='api-docs.json') {
    $filePath = Config::get('swaggervel.doc-dir') . "/{$page}";

    if (File::extension($filePath) === "") {
        $filePath .= ".json";
    }
    if (!File::Exists($filePath)) {
        App::abort(404, "Cannot find {$filePath}");
    }

    $content = File::get($filePath);
    return Response::make($content, 200, array(
        'Content-Type' => 'application/json'
    ));
});

Route::get(Config::get('swaggervel.api-docs-route'), function() {
    if (Config::get('swaggervel.generateAlways')) {
        $appDir = base_path()."/".Config::get('swaggervel.app-dir');
        $docDir = Config::get('swaggervel.doc-dir');

        if (!File::exists($docDir) || is_writable($docDir)) {
            // delete all existing documentation
            if (File::exists($docDir)) {
                File::deleteDirectory($docDir);
            }

            File::makeDirectory($docDir);

            $defaultBasePath = Config::get('swaggervel.default-base-path');
            $defaultApiVersion = Config::get('swaggervel.default-api-version');
            $defaultSwaggerVersion = Config::get('swaggervel.default-swagger-version');
            $excludeDirs = Config::get('swaggervel.excludes');

            $swagger =  \Swagger\scan($appDir, [
                'exclude' => $excludeDirs
            ]);

            $filename = $docDir . '/api-docs.json';
            file_put_contents($filename, $swagger);
        }
    }

    if (Config::get('swaggervel.behind-reverse-proxy')) {
        $proxy = Request::server('REMOTE_ADDR');
        Request::setTrustedProxies(array($proxy));
    }

    Blade::setEscapedContentTags('{{{', '}}}');
    Blade::setContentTags('{{', '}}');

    //need the / at the end to avoid CORS errors on Homestead systems.
    $response = response()->view('swaggervel::index', array(
            'secure'         => Request::secure(),
            'urlToDocs'      => Config::get('swaggervel.doc-route'),
            'requestHeaders' => Config::get('swaggervel.requestHeaders'),
            'clientId'       => Request::input("client_id"),
            'clientSecret'   => Request::input("client_secret"),
            'realm'          => Request::input("realm"),
            'appName'        => Request::input("appName"),
        )
    );

    //need the / at the end to avoid CORS errors on Homestead systems.
    /*$response = Response::make(
        View::make('swaggervel::index', array(
                'secure'         => Request::secure(),
                'urlToDocs'      => url(Config::get('swaggervel.doc-route')),
                'requestHeaders' => Config::get('swaggervel.requestHeaders') )
        ),
        200
    );*//*

    if (Config::has('swaggervel.viewHeaders')) {
        foreach (Config::get('swaggervel.viewHeaders') as $key => $value) {
            $response->header($key, $value);
        }
    }

    return $response;
});
*/
