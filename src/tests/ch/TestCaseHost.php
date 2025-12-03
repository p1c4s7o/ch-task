<?php

namespace Tests\Ch;

use App\Contracts\Nginx\CommandServiceInterface;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCaseHost extends BaseTestCase
{
//    protected function setUp(): void
//    {
//        parent::setUp();
//        $this->mock(CommandServiceInterface::class, function (MockInterface&CreateDomainAction $mock) {
//            $mock->shouldReceive('execute')
//                ->once()
//                ->andReturn(ApiResult::created('http://test.localhost'));
//        });
//    }
}
