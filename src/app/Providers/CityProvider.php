<?php

namespace app\Providers;

use App\Contracts\Nginx\CommandExecutorInterface;
use App\Contracts\Nginx\CommandMiddlewareInterface;
use App\Contracts\Nginx\CommandServiceInterface;
use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Factories\NginxHostServiceFactory;
use App\Domain\Nginx\Factories\NginxStoreServiceFactory;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\Tests\DomainV3Controller;
use App\Services\Nginx\CommandPipeline;
use App\Services\Nginx\NginxCommandService;
use App\Services\Nginx\NginxHost;
use App\Services\Nginx\NginxRedisCommandService;
use App\Services\Nginx\NginxStore;
use Exception;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Filesystem\Filesystem;

class CityProvider extends ServiceProvider
{
    /**
     * @throws Exception
     */
    public function register(): void
    {
        $stub_path = config('cth.stubs_path');
        $nginx_dir_conf = config('cth.domains_path');
        $public_html = config('cth.public_html');
        $use_redis = config('nginx.use_redis', false);

        if (! $stub_path) {
            throw new Exception('Dir with stubs not found');
        }

        if (! $nginx_dir_conf) {
            throw new Exception('Dir with nginx config not found');
        }

        if (! $public_html) {
            throw new Exception('Dir public_html not found');
        }

        if ($use_redis) {
            $this->app->bind(NginxCommandService::class, NginxRedisCommandService::class);
        }

        $this->app->singleton(CommandServiceInterface::class, function () {
            $map_middleware = config('nginx.middleware', []);
            $pipeline = new CommandPipeline;

            foreach ($map_middleware as $middleware) {
                if (! is_subclass_of($middleware, CommandMiddlewareInterface::class)) {
                    continue;
                }

                $pipeline->pipe(
                    fn ($cmd, $next) => (new $middleware)->handle($cmd, $next)
                );
            }

            $use_strategy = config('nginx.driver');
            $map_strategy = config('nginx.strategy', []);

            if (! $use_strategy || ! is_array($map_strategy) ||
                count($map_strategy) < 1 ||
                ! array_key_exists($use_strategy, $map_strategy)) {
                throw new Exception('Invalid configuration: nginx.driver or nginx.strategy is missing or empty.');
            }

            $strategy = new $map_strategy[$use_strategy];


            $executor = config('nginx.executor');
            if(! $executor)
                throw new Exception('Executor is null. Must be App\Contracts\Nginx\CommandExecutorInterface');

            $executor = $this->app->make($executor);
            if(! is_subclass_of($executor, CommandExecutorInterface::class))
                throw new Exception('Not implemented App\Contracts\Nginx\CommandExecutorInterface');


            return $this->app->make(NginxCommandService::class, [
                'strategy' => $strategy,
                'pipeline' => $pipeline,
                'executor' => $executor,
            ]);
        });
        $this->app->singleton(NginxHostServiceFactory::class, function () use ($stub_path, $nginx_dir_conf) {
            $nginx_base_dir_conf = rtrim($nginx_dir_conf, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

            if (! file_exists($nginx_base_dir_conf.'v1')) {
                if (! mkdir($nginx_base_dir_conf.'v1', 0755)) {
                    throw new Exception('Cannot create directory '.$nginx_base_dir_conf.'v1');
                }
            }

            if (! file_exists($nginx_base_dir_conf.'v2')) {
                if (! mkdir($nginx_base_dir_conf.'v2', 0755)) {
                    throw new Exception('Cannot create directory '.$nginx_base_dir_conf.'v2');
                }
            }

            $host_v1 = new NginxHost(
                $this->app->make(Filesystem::class),
                $stub_path,
                $nginx_base_dir_conf.'v1',
                NginxApiVersion::V1
            );

            $host_v2 = new NginxHost(
                $this->app->make(Filesystem::class),
                $stub_path,
                $nginx_base_dir_conf.'v2',
                NginxApiVersion::V2
            );

            return new NginxHostServiceFactory($host_v1, $host_v2);
        });
        $this->app->singleton(NginxStoreServiceFactory::class, function () use ($stub_path, $public_html) {
            $public_html_dir_conf = rtrim($public_html, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

            $store_v1 = new NginxStore(
                $this->app->make(Filesystem::class),
                $stub_path,
                $public_html_dir_conf,
                NginxApiVersion::V1
            );

            $store_v2 = new NginxStore(
                $this->app->make(Filesystem::class),
                $stub_path,
                $public_html_dir_conf,
                NginxApiVersion::V2
            );

            return new NginxStoreServiceFactory($store_v1, $store_v2);
        });

        $v3_api_enable = config('cth.v3_api_enable');
        if ((bool) $v3_api_enable) {
            $this->app->bind(DomainController::class, DomainV3Controller::class);
        }

    }
}
