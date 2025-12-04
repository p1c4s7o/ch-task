<?php

namespace tests\Unit\Actions;

use App\Actions\Domain\GetDomainStatusAction;
use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Exceptions\HostException;
use App\Services\Nginx\NginxHost;
use App\Services\Nginx\NginxStore;
use Exception;
use Mockery;
use tests\TestCaseHost;


class GetDomainStatusActionUnitTest extends TestCaseHost
{
    private function mockHostFactory($host): void
    {
        $this->mock(\App\Domain\Nginx\Factories\NginxHostServiceFactory::class, function ($mock) use ($host) {
            $mock->shouldReceive('get')->andReturn($host);
        });
    }

    private function mockStoreFactory($store): void
    {
        $this->mock(\App\Domain\Nginx\Factories\NginxStoreServiceFactory::class, function ($mock) use ($store) {
            $mock->shouldReceive('get')->andReturn($store);
        });
    }

    private function makeAction(): GetDomainStatusAction
    {
        return $this->app->make(GetDomainStatusAction::class);
    }





    /** TESTS */

    public function test_domain_exists_returns_exists_result()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->with('test', null)->andReturn(true);
        $host->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);
        $host->shouldReceive('getHttpDomainName')->andReturn('test.localhost');

        $store = Mockery::mock(NginxStore::class);
        $store->shouldReceive('exists')->with('test', null)->andReturn(true);

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $action = $this->makeAction();
        $result = $action->handle(NginxApiVersion::V1, 'test');

        $this->assertTrue($result->exists);
        $this->assertStringContainsString('http://test.localhost', $result->link);
    }

    public function test_domain_not_found_returns_error()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->with('test', null)->andReturn(false);
        $host->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);

        $store = Mockery::mock(NginxStore::class);
        $store->shouldReceive('exists')->with('test', null)->andReturn(false);

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $action = $this->makeAction();
        $result = $action->handle(NginxApiVersion::V1, 'test');

        $this->assertFalse($result->exists);
        $this->assertEquals(404, $result->status_code);
        $this->assertEquals('Domain not found', $result->message);
    }

    public function test_host_exception_propagates()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andThrow(new HostException("boom"));
        $host->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);

        $store = Mockery::mock(NginxStore::class);

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $this->expectException(HostException::class);
        $this->expectExceptionMessage("boom");

        $action = $this->makeAction();
        $action->handle(NginxApiVersion::V1, 'test');
    }

    public function test_generic_exception_propagates()
    {
        $host = Mockery::mock(NginxHost::class);
        $host->shouldReceive('exists')->andThrow(new Exception("fail"));
        $host->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);

        $store = Mockery::mock(NginxStore::class);

        $this->mockHostFactory($host);
        $this->mockStoreFactory($store);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("fail");

        $action = $this->makeAction();
        $action->handle(NginxApiVersion::V1, 'test');
    }
}
