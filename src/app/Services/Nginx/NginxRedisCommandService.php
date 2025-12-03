<?php

namespace App\Services\Nginx;

use App\Contracts\Nginx\CommandExecutorInterface;
use App\Contracts\Nginx\CommandPipelineInterface;
use App\Contracts\Nginx\CommandStrategyInterface;
use App\Domain\Nginx\ValueObjects\NginxCommandResult;
use App\Exceptions\NginxRedisException;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Illuminate\Support\Facades\Redis;

class NginxRedisCommandService extends NginxCommandService
{
    protected string $key = 'ngnx:op';

    protected string $redis_server = 'default';

    /**
     * @var int in seconds
     */
    protected int $ttl = 10;

    public function getKeyAction(): string
    {
        return $this->key;
    }

    /**
     * @var array|\Closure[]
     */
    protected array $_rules = [

    ];

    /**
     * @throws NginxRedisException
     */
    private function scheduleAction(string $action, string $message): bool
    {
        /** @var PhpRedisConnection $redis */
        $redis = Redis::connection($this->redis_server);
        $key = $this->getKeyAction();
        $value = $redis->get($key);

        if (! $value) {
            $redis->set($key, $action, 'EX', $this->ttl);

            return true;
        }

        if ($value === $action) {
            throw new NginxRedisException($message, 200);
        }

        return false;
    }

    public function __construct(CommandStrategyInterface $strategy, CommandPipelineInterface $pipeline, CommandExecutorInterface $executor)
    {
        // TODO Mapping rules
        $this->_rules = [
            'reload' => fn ($a) => $this->scheduleAction($a, 'Reload has already been scheduled'),
            'stop' => fn ($a) => $this->scheduleAction($a, 'Stop has already been scheduled'),
            'restart' => fn ($a) => $this->scheduleAction($a, 'Restart has already been scheduled'),
            'start' => fn ($a) => $this->scheduleAction($a, 'Start has already been scheduled'),
        ];

        parent::__construct($strategy, $pipeline, $executor);
    }

    /**
     * @throws NginxRedisException
     */
    public function execute(string $action): NginxCommandResult
    {

        // TODO composer require predis/predis
        if (! array_key_exists($action, $this->_rules)) {
            return parent::execute($action);
        }

        if (! $this->_rules[$action]($action)) {
            throw new NginxRedisException('Previous operation is still in progress', 400);
        }

        // TODO OR USE
        try {
            return parent::execute($action);
        } finally {
            /** @var Connection $redis */
            $redis = Redis::connection($this->redis_server);
            $redis->del($this->getKeyAction());
        }

        //        $res = parent::execute($action);
        //
        //        /** @var Connection $redis */
        //        $redis = Redis::connection($this->redis_server);
        //        $redis->del($this->getKeyAction());
        //
        //        return $res;
    }
}
