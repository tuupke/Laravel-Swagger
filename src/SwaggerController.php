<?php namespace Tuupke\Swagger;

use Illuminate\Routing\Controller;
use Config;
use File;
use OpenApi;
use Redis;

class SwaggerController extends Controller {
    public function ui($environment = null) {
        $env = $this->loadEnvironment($environment);

        $swagger_doc_dir = Config::get('swagger.docs-dir') . '/' . strtolower($env['name']);

        $base = Config::get('swagger.ui-route', 'spec');
        $docs = Config::get('swagger.docs-route', 'spec');

        $defData = ["api_docs" => "/$base/$docs/" . strtolower($env['name'])];
        $viewRenderer = @$env['view-renderer'] ?? @Config::get('swagger')["view-renderer"];
        if (is_null($viewRenderer))
            return view('package-swagger::main', $defData);

        return $viewRenderer($env, $defData);
    }

    public function docs($environment = null) {
        $env = $this->loadEnvironment($environment);

        $docLoc = Config::get('swagger.docs-dir') . "/".$env["name"];

        $store = (is_null($lval = @$env['always-generate']) && (is_null($val = Config::get('swagger.always-generate')) || $val === false)) || $lval === false;
        // $reqGen = @$env['always-generate'] || Config::get('');
        if ($store && \File::exists($docLoc))
            return \File::get($docLoc);

        $excludeDirs = @$env['excludes'] ?? [];
        $appDir = $env['base-annotations-dir'] ?? Config::get('swagger.defaults.base-annotations-dir');
        $resp = OpenApi\scan($appDir, [
            'exclude' => Util::includeResolver($excludeDirs),
        ])->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($store)
            \File::put(Util::ensureDir($docLoc), $resp);

        return response($resp, 200, ['Content-Type' => 'application/json']);
    }

    private function loadEnvironment($environment = null) {
        $specs = Config::get('swagger.swagger-defs');
        $env = $specs[@$environment ?? Config::get('swagger.default-swagger-def')];
        if (is_null($env))
            abort(404, "Def not found; $environment");

        return $env;
    }
}
