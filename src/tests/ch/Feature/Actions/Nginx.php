<?php

namespace Tests\Ch\Feature\Actions;

use App\Contracts\Nginx\CommandServiceInterface;
use App\Domain\Nginx\Factories\NginxHostServiceFactory;
use App\Domain\Nginx\Factories\NginxStoreServiceFactory;
use App\Domain\Nginx\ValueObjects\NginxCommandResult;
use App\Services\Nginx\NginxHost;
use App\Services\Nginx\NginxStore;
use Mockery\MockInterface;
use Tests\ch\TestCaseHost;

class Nginx extends TestCaseHost
{
    protected function assertSuccessResponse($response): void
    {
        $response->assertStatus(200)
            ->assertJson(['status' => true, 'message' => "", 'error' => ""]);
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(NginxHost::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturn(false);
            $mock->shouldReceive('create')->andReturn(true);
            $mock->shouldReceive('delete')->andReturn(true);
            $mock->shouldReceive('getHttpDomainName')->andReturn('test.localhost');
            $mock->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);
        });

        $this->mock(NginxStore::class, function (MockInterface $mock) {
            $mock->shouldReceive('createIndexFile')->andReturn(true);
            $mock->shouldReceive('delete')->andReturn(true);
            $mock->shouldReceive('exists')->andReturn(true);
        });

        $this->mock(CommandServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('execute')
                ->andReturn(new NginxCommandResult(true, '', ''))
                ->byDefault();
        });

        $this->mock(NginxHostServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxHost::class));
        });

        $this->mock(NginxStoreServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxStore::class));
        });
    }

    public function test_nginx_start_error()
    {
        $this->mock(CommandServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('execute')
                ->andReturn(new NginxCommandResult(false, '', ''))
                ->byDefault();
        });
        $response = $this->postJson('/api/server/start');
        $response->assertStatus(500)
            ->assertJson(['status' => false, 'message' => "", 'error' => ""]);
    }

    public function test_nginx_start()
    {
        $response = $this->postJson('/api/server/start');
        $this->assertSuccessResponse($response);
    }

    public function test_nginx_restart()
    {
        $response = $this->postJson('/api/server/restart');
        $this->assertSuccessResponse($response);
    }

    public function test_nginx_status()
    {
        $response = $this->getJson('/api/server/status');
        $this->assertSuccessResponse($response);
    }

    public function test_nginx_stop()
    {
        $response = $this->postJson('/api/server/stop');
        $this->assertSuccessResponse($response);
    }

    public function test_nginx_reload()
    {
        $response = $this->postJson('/api/server/reload');
        $this->assertSuccessResponse($response);
    }

}
