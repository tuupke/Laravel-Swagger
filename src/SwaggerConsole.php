<?php

namespace Tuupke\Swagger;

class SwaggerConsole extends \Illuminate\Console\Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger {environment?} {--generate} {--yaml} {--storageprefix=} {--stdout} {--nostore} {--list} {--skip=*}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check swagger/openapi configs';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $config = $this->argument('environment');
        $generate = $this->option("generate");
        $yaml = $this->option("yaml");
        $list = $this->option("list");
        $stdout = $this->option("stdout");
        $nostore = $this->option("nostore");
        $skips = array_flip($this->option("skip"));
        $storage = $this->option("storageprefix") ?? \Config::get('swagger.docs-dir') ?? storage_path();
        if ($storage[0] !== "/") {
            $storage = storage_path() . "/$storage";
        }

        // Load the configs
        $envs = \Config::get('swagger.swagger-defs');
        if (!is_null($config)) {
            $env = $envs[$config];
            if (is_null($env)) {
                return $this->error("Could not load environment $config");
            }

            $envs = [$config => $env];
        }

        if (count($envs) == 0) {
            return $this->warn("No environments run");
        }

        // For every loaded environment run everything
        $errs = [];
        foreach ($envs as $envName => $env) {
            if (key_exists($envName, $skips)) {
                if ($list) {
                    $this->info("Skipped environment: $envName");
                }

                continue;
            }

            if ($list) {
                $this->info("Checking environment: $envName");
            }

            $excludeDirs = @$env['excludes'] ?? [];
            $appDir = $env['base-annotations-dir'] ?? \Config::get('swagger.defaults.base-annotations-dir');

            try {
                // Attempt to scan
                $scan = \OpenApi\scan($appDir, ['exclude' => Util::includeResolver($excludeDirs)]);
                if ($list) {
                    $this->info("No error found for environment: $envName");
                }

                if ($generate && (!$nostore || $stdout)) {
                    // Generate the contents
                    $contents = $yaml
                        ? $scan->toYaml()
                        : $scan->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                    if (!$nostore) {
                        \File::put(Util::ensureDir("$storage/$envName"), $contents);
                        $this->info("Stored contents of environment: $envName; at: $storage/$envName");
                    }

                    if ($stdout) {
                        $this->line($contents);
                    }
                }
            } catch (\Exception $e) {
                $errs[$envName] = $e->getMessage();

                if ($list) {
                    $this->warn("Error found for environment: $envName");
                }
            }
        }

        if (count($errs)) {
            foreach ($errs as $name => $err) {
                $this->line("Error for environment '$name':\n\t" . wordwrap($err, 75, "\n\t") . "\n");
            }

            return 42;
        }
    }
}
