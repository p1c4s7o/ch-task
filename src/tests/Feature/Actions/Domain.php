<?php

namespace tests\Feature\Actions;

use App\Actions\Domain\GetDomainStatusAction;
use App\Contracts\Nginx\CommandServiceInterface;
use App\Domain\Nginx\Factories\NginxHostServiceFactory;
use App\Domain\Nginx\Factories\NginxStoreServiceFactory;
use App\Domain\Nginx\ValueObjects\NginxCommandResult;
use App\Helpers\ApiResult;
use App\Services\Nginx\NginxHost;
use App\Services\Nginx\NginxStore;
use Mockery\MockInterface;
use tests\TestCaseHost;

class Domain extends TestCaseHost
{

    /** @var CommandServiceInterface&MockInterface */
    protected MockInterface $commandServiceMock;
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

        $this->commandServiceMock = $this->mock(CommandServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('execute')
                ->withArgs(function ($arg) {
                    return in_array($arg, ['test', 'reload']);
                })
                ->andReturn(new NginxCommandResult(false, '', ''))
                ->byDefault();
        });

    }

    /**
     * Create domain
     */


    public function test_create_domain_v1_success()
    {
        $payload = [
            'domain' => 'test'
        ];

        $this->mock(NginxHostServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxHost::class));
        });

        $this->mock(NginxStoreServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxStore::class));
        });

        $this->commandServiceMock = $this->mock(CommandServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('execute')
                ->withArgs(function ($arg) {
                    return in_array($arg, ['test', 'reload']);
                })
                ->andReturn(new NginxCommandResult(true, '', ''))
                ->byDefault();
        });

        $response = $this->postJson('/api/v1/domain/create', $payload);

        $this->commandServiceMock->shouldHaveReceived('execute')->with('test');
        $this->commandServiceMock->shouldHaveReceived('execute')->with('reload');

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => "Domain created",
                'data' => [
                    'link' => 'http://test.localhost',
                ]
            ]);
    }

    public function test_create_domain_v2_success()
    {
        $payload = [
            'domain' => 'test',
            'port' => 8888
        ];

        $this->mock(NginxHost::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturn(false);
            $mock->shouldReceive('create')->andReturn(true);
            $mock->shouldReceive('delete')->andReturn(true);
            $mock->shouldReceive('getHttpDomainName')->andReturn('test.localhost:8888');
            $mock->shouldReceive('splitDomainAndPort')->andReturn(['test', 8888]);
        });

        $this->mock(NginxHostServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxHost::class));
        });

        $this->mock(NginxStoreServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxStore::class));
        });

        $this->commandServiceMock = $this->mock(CommandServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('execute')
                ->withArgs(function ($arg) {
                    return in_array($arg, ['test', 'reload']);
                })
                ->andReturn(new NginxCommandResult(true, '', ''))
                ->byDefault();
        });

        $response = $this->postJson('/api/v2/domain/create', $payload);

        $this->commandServiceMock->shouldHaveReceived('execute')->with('test');
        $this->commandServiceMock->shouldHaveReceived('execute')->with('reload');

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => "Domain created",
                'data' => [
                    'link' => 'http://' . $payload['domain'] . '.localhost:' . $payload['port'],
                ]
            ]);
    }

    public function test_create_domain_v1_already_exists()
    {
        $this->mock(NginxHost::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturn(true);
            $mock->shouldReceive('create')->andReturn(true);
            $mock->shouldReceive('delete')->andReturn(true);
            $mock->shouldReceive('getHttpDomainName')->andReturn('test.localhost');
            $mock->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);
        });

        $this->mock(CommandServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('execute')
                ->withArgs(function ($arg) {
                    return in_array($arg, ['test', 'reload']);
                })
                ->andReturn(new NginxCommandResult(true, '', ''))
                ->byDefault();
        });

        $this->mock(NginxHostServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxHost::class));
        });

        $this->mock(NginxStoreServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxStore::class));
        });

        $payload = [
            'domain' => 'test'
        ];

        $response = $this->postJson('/api/v1/domain/create', $payload);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Domain already exists',
            ]);
    }

    public function test_create_domain_v2_already_exists()
    {
        $this->mock(NginxHost::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturn(true);
            $mock->shouldReceive('create')->andReturn(true);
            $mock->shouldReceive('delete')->andReturn(true);
            $mock->shouldReceive('getHttpDomainName')->andReturn('test.localhost:8888');
            $mock->shouldReceive('splitDomainAndPort')->andReturn(['test', 8888]);
        });

        $this->mock(CommandServiceInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('execute')
                ->withArgs(function ($arg) {
                    return in_array($arg, ['test', 'reload']);
                })
                ->andReturn(new NginxCommandResult(true, '', ''))
                ->byDefault();
        });

        $this->mock(NginxHostServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxHost::class));
        });

        $this->mock(NginxStoreServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxStore::class));
        });

        $payload = [
            'domain' => 'test'
        ];

        $response = $this->postJson('/api/v2/domain/create', $payload);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Domain already exists',
            ]);
    }

    /**
     * Status domain
     */

    public function test_get_status_domain_v1()
    {
        $payload = [
            'domain' => 'test'
        ];

        $this->mock(GetDomainStatusAction::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle')
                ->andReturn(ApiResult::exists('test'));
        });

        $this->mock(NginxHost::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturn(true);
            $mock->shouldReceive('create')->andReturn(true);
            $mock->shouldReceive('delete')->andReturn(true);
            $mock->shouldReceive('getHttpDomainName')->andReturn('test.localhost');
            $mock->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);
        });

        $response = $this->getJson('/api/v1/status/test', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_get_status_domain_v1_head()
    {
        $payload = [
            'domain' => 'test'
        ];

        $this->mock(GetDomainStatusAction::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle')
                ->andReturn(ApiResult::exists('test'));
        });

        $this->mock(NginxHost::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturn(true);
            $mock->shouldReceive('create')->andReturn(true);
            $mock->shouldReceive('delete')->andReturn(true);
            $mock->shouldReceive('getHttpDomainName')->andReturn('test.localhost');
            $mock->shouldReceive('splitDomainAndPort')->andReturn(['test', null]);
        });

        $response = $this->head('/api/v1/status/test', $payload);
        $response->assertStatus(200);
    }

    public function test_get_status_domain_v2()
    {
        $this->mock(GetDomainStatusAction::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle')
                ->andReturn(ApiResult::exists('test:8888'))->once();
        });

        $response = $this->getJson('/api/v2/status/test:8888');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_get_status_domain_v2_head()
    {
        $this->mock(GetDomainStatusAction::class, function (MockInterface $mock) {
            $mock->shouldReceive('handle')
                ->andReturn(ApiResult::exists('test'))->once();
        });

        $response = $this->head('/api/v2/status/test:8888');
        $response->assertStatus(200);
    }

    /**
     * Delete domain
     */

    public function test_delete_domain_v1()
    {
        $this->mock(NginxHost::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->andReturn(true);
            $mock->shouldReceive('exists')->andReturn(true)->once();
            $mock->shouldReceive('delete')->andReturn(true)->once();
            $mock->shouldReceive('getHttpDomainName')->andReturn('test.localhost');
            $mock->shouldReceive('splitDomainAndPort')->andReturn(['test', null])->once();
        });

        $this->mock(NginxHostServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxHost::class));
        });

        $this->mock(NginxStoreServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxStore::class));
        });

        $response = $this->deleteJson('/api/v1/domain/test');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_delete_domain_v2()
    {
        $this->mock(NginxHost::class, function (MockInterface $mock) {
            $mock->shouldReceive('create')->andReturn(true);
            $mock->shouldReceive('exists')->andReturn(true)->once();
            $mock->shouldReceive('delete')->andReturn(true)->once();
            $mock->shouldReceive('getHttpDomainName')->andReturn('test.localhost:8888');
            $mock->shouldReceive('splitDomainAndPort')
                ->with('test:8888')
                ->andReturn(['test', 8888])
                ->once();
        });

        $this->mock(NginxHostServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxHost::class));
        });

        $this->mock(NginxStoreServiceFactory::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->andReturn($this->app->make(NginxStore::class));
        });

        $response = $this->deleteJson('/api/v1/domain/test:8888');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

}
