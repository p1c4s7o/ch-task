<?php

namespace tests\Unit\Actions;

use App\Actions\Domain\DeleteDomainAction;
use App\Contracts\Nginx\CommandServiceInterface;
use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Exceptions\HostException;
use App\Domain\Nginx\Exceptions\IOError;
use App\Domain\Nginx\Factories\NginxHostServiceFactory;
use App\Domain\Nginx\Factories\NginxStoreServiceFactory;
use App\Domain\Nginx\ValueObjects\NginxCommandResult;
use App\Services\Nginx\NginxHost;
use App\Services\Nginx\NginxStore;
use Exception;
use Mockery;
use tests\TestCaseHost;

class DeleteDomainActionUnitTest extends TestCaseHost
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

    private function mockCommand($command): void
    {
        $this->mock(CommandServiceInterface::class, function ($mock) use ($command) {
            $mock->shouldReceive('execute')
                ->andReturnUsing(fn(...$args) => $command->execute(...$args));
        });
    }

    private function makeAction(): DeleteDomainAction
    {
        return $this->app->make(DeleteDomainAction::class);
    }





    /** TESTS */

    public function test_domain_not_found_throws_host_exception()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->with('test', null)->andReturn(false);
        $host->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);

        $store = Mockery::mock(NginxStore::class);

        $command = Mockery::mock(CommandServiceInterface::class);

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);
        $this->mockCommand($command);

        $this->expectException(HostException::class);
        $this->expectExceptionMessage('Domain not found');

        $action = $this->makeAction();
        $action->handle(NginxApiVersion::V1, 'test');
    }

    public function test_success_delete_calls_host_store_and_reload()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->with('test', null)->andReturn(true);
        $host->shouldReceive('delete')->with('test', null)->once();
        $host->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);

        $store = Mockery::mock(NginxStore::class);
        $store->shouldReceive('delete')->with('test', null)->once();

        $command = Mockery::mock(CommandServiceInterface::class);
        $command->shouldReceive('execute')
            ->with('reload')
            ->once()
            ->andReturn(new NginxCommandResult(true, '', ''));

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);
        $this->mockCommand($command);

        $action = $this->makeAction();

        $action->handle(NginxApiVersion::V1, 'test');

        $this->assertTrue(true);
    }

    public function test_host_exception_propagates()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andThrow(new HostException("boom"));
        $host->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);

        $store = Mockery::mock(NginxStore::class);

        $command = Mockery::mock(CommandServiceInterface::class);

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);
        $this->mockCommand($command);

        $this->expectException(HostException::class);
        $this->expectExceptionMessage("boom");

        $action = $this->makeAction();
        $action->handle(NginxApiVersion::V1, 'test');
    }

    public function test_io_error_propagates()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andThrow(new IOError("fs error"));
        $host->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);

        $store = Mockery::mock(NginxStore::class);

        $command = Mockery::mock(CommandServiceInterface::class);

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);
        $this->mockCommand($command);

        $this->expectException(IOError::class);
        $this->expectExceptionMessage("fs error");

        $action = $this->makeAction();
        $action->handle(NginxApiVersion::V1, 'test');
    }

    public function test_generic_exception_propagates()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andThrow(new Exception("fail"));
        $host->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);

        $store = Mockery::mock(NginxStore::class);

        $command = Mockery::mock(CommandServiceInterface::class);

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);
        $this->mockCommand($command);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("fail");

        $action = $this->makeAction();
        $action->handle(NginxApiVersion::V1, 'test');
    }

    public function test_invalid_host_name()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andReturn(false);
        $host->shouldReceive('splitDomainAndPort')->andThrow(new HostException("Invalid name: allowed only a-z, 0-9, -"));

        $store = Mockery::mock(NginxStore::class);

        $command = Mockery::mock(CommandServiceInterface::class);

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);
        $this->mockCommand($command);

        $this->expectException(HostException::class);
        $this->expectExceptionMessage("Invalid name: allowed only a-z, 0-9, -");

        $action = $this->makeAction();
        $action->handle(NginxApiVersion::V1, '_test');
    }
}
