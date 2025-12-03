<?php

namespace Tests\Ch\Unit\Actions;

use App\Actions\Domain\CreateDomainAction;
use App\Contracts\Nginx\CommandServiceInterface;
use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Exceptions\HostException;
use App\Domain\Nginx\Exceptions\IOError;
use App\Domain\Nginx\Factories\NginxHostServiceFactory;
use App\Domain\Nginx\Factories\NginxStoreServiceFactory;
use App\Domain\Nginx\ValueObjects\NginxCommandResult;
use App\Exceptions\NginxRedisException;
use App\Services\Nginx\NginxHost;
use App\Services\Nginx\NginxStore;
use Exception;
use Mockery;
use PHPUnit\Event\RuntimeException;
use Tests\ch\TestCaseHost;

class CreateDomainActionUnitTest extends TestCaseHost
{
    private function mockHostFactory($host): void
    {
        $this->mock(NginxHostServiceFactory::class, function ($mock) use ($host) {
            $mock->shouldReceive('get')->andReturn($host);
        });
    }

    private function mockStoreFactory($store): void
    {
        $this->mock(NginxStoreServiceFactory::class, function ($mock) use ($store) {
            $mock->shouldReceive('get')->andReturn($store);
        });
    }

    private function mockCommandService($command): void
    {
        $this->mock(CommandServiceInterface::class, function ($mock) use ($command) {
            $mock->shouldReceive('execute')
                ->andReturnUsing(fn (...$args) => $command->execute(...$args));
        });
    }

    private function makeAction(): CreateDomainAction
    {
        return $this->app->make(CreateDomainAction::class);
    }

    /** TESTS */
    public function test_domain_exists_returns_exists_result()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->with('test', null)->andReturn(true);
        $host->shouldReceive('getHttpDomainName')->andReturn('test.localhost');

        $store = Mockery::mock(NginxStore::class);

        $command = Mockery::mock(CommandServiceInterface::class);
        $command->shouldReceive('execute')->andThrow(new RuntimeException('unexpected'));

        $this->mockCommandService($command);
        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $action = $this->makeAction();
        $result = $action->handle(NginxApiVersion::V1, 'test');

        $this->assertEquals('http://test.localhost', $result->link);
    }

    public function test_host_create_failed()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andReturn(false);
        $host->shouldReceive('create')->andReturn(false);
        $store = Mockery::mock(NginxStore::class);

        $command = Mockery::mock(CommandServiceInterface::class);
        $command->shouldReceive('execute')->andThrow(new RuntimeException('unexpected'));

        $this->mockCommandService($command);
        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $action = $this->makeAction();
        $result = $action->handle(NginxApiVersion::V1, 'test');

        $this->assertEquals('500', $result->status_code);
        $this->assertEquals('Domain creation failed', $result->message);
    }

    public function test_store_index_failed()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andReturn(false);
        $host->shouldReceive('create')->andReturn(true);

        $store = Mockery::mock(NginxStore::class);
        $store->shouldReceive('createIndexFile')->andReturn(false);

        $command = Mockery::mock(CommandServiceInterface::class);
        $command->shouldReceive('execute')->andThrow(new RuntimeException('unexpected'));

        $this->mockCommandService($command);
        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $action = $this->makeAction();
        $result = $action->handle(NginxApiVersion::V1, 'test');

        $this->assertEquals('500', $result->status_code);
        $this->assertEquals('Index file creation failed.', $result->message);
    }

    public function test_nginx_test_failed_cleans_up()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andReturn(false);
        $host->shouldReceive('create')->andReturn(true);
        $host->shouldReceive('delete')->once();

        $store = Mockery::mock(NginxStore::class);
        $store->shouldReceive('createIndexFile')->andReturn(true);
        $store->shouldReceive('delete')->once();

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $cmdFailed = new NginxCommandResult(false, 'error', 'out');
        $command = Mockery::mock(CommandServiceInterface::class);
        $command->shouldReceive('execute')->with('test')->andReturn($cmdFailed);

        $this->mockCommandService($command);

        $action = $this->makeAction();
        $result = $action->handle(NginxApiVersion::V1, 'test');

        $this->assertEquals('500', $result->status_code);
        $this->assertEquals('Nginx config test failed', $result->message);
    }

    public function test_nginx_reload_redis_exception_returns_queued()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andReturn(false);
        $host->shouldReceive('create')->andReturn(true);
        $host->shouldReceive('getHttpDomainName')->andReturn('test.localhost');

        $store = Mockery::mock(NginxStore::class);
        $store->shouldReceive('createIndexFile')->andReturn(true);

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $command = Mockery::mock(CommandServiceInterface::class);
        $command->shouldReceive('execute')
            ->with('test')
            ->andReturn(new NginxCommandResult(true, '', ''));
        $command->shouldReceive('execute')
            ->with('reload')
            ->andThrow(new NginxRedisException('queued'));

        $this->mockCommandService($command);

        $action = $this->makeAction();
        $result = $action->handle(NginxApiVersion::V1, 'test');

        $this->assertEquals('true', $result->queued);
        $this->assertEquals('http://test.localhost', $result->link);
    }

    public function test_success_created()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andReturn(false);
        $host->shouldReceive('create')->andReturn(true);
        $host->shouldReceive('getHttpDomainName')->andReturn('test.localhost');

        $store = Mockery::mock(NginxStore::class);
        $store->shouldReceive('createIndexFile')->andReturn(true);

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $command = Mockery::mock(CommandServiceInterface::class);
        $command->shouldReceive('execute')->with('test')
            ->andReturn(new NginxCommandResult(true, '', ''));
        $command->shouldReceive('execute')->with('reload')
            ->andReturn(new NginxCommandResult(true, '', ''));

        $this->mockCommandService($command);

        $action = $this->makeAction();
        $result = $action->handle(NginxApiVersion::V1, 'test');

        $this->assertEquals('true', $result->created);
    }

    public function test_host_exception_returns_error()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andThrow(new HostException('boom'));

        $store = Mockery::mock(NginxStore::class);

        $command = Mockery::mock(CommandServiceInterface::class);
        $command->shouldReceive('execute')->andThrow(new RuntimeException('unexpected'));

        $this->mockCommandService($command);
        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $action = $this->makeAction();
        $result = $action->handle(NginxApiVersion::V1, 'test');

        $this->assertEquals('500', $result->status_code);
        $this->assertEquals('boom', $result->message);
    }

    public function test_io_error_returns_error()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andThrow(new IOError('fs error'));

        $store = Mockery::mock(NginxStore::class);

        $command = Mockery::mock(CommandServiceInterface::class);
        $command->shouldReceive('execute')->andThrow(new RuntimeException('unexpected'));

        $this->mockCommandService($command);
        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $action = $this->makeAction();
        $result = $action->handle(NginxApiVersion::V1, 'test');

        $this->assertEquals('500', $result->status_code);
        $this->assertEquals('I/O Error', $result->message);
    }

    public function test_generic_exception_returns_internal_error()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andThrow(new Exception('fail'));

        $store = Mockery::mock(NginxStore::class);

        $command = Mockery::mock(CommandServiceInterface::class);
        $command->shouldReceive('execute')->andThrow(new RuntimeException('unexpected'));

        $this->mockCommandService($command);
        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $action = $this->makeAction();
        $result = $action->handle(NginxApiVersion::V1, 'test');

        $this->assertEquals('500', $result->status_code);
        $this->assertEquals('Internal Server Error', $result->message);
    }
}
