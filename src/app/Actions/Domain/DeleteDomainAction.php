<?php

namespace App\Actions\Domain;

use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Exceptions\HostException;
use App\Domain\Nginx\Exceptions\IOError;
use App\Services\Nginx\NginxHost;
use App\Services\Nginx\NginxStore;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeleteDomainAction extends BaseAction
{
    /**
     * @throws HostException
     * @throws Throwable
     * @throws IOError
     */
    public function handle(NginxApiVersion $version, string $raw_domain): void
    {
        [$domain, $port] = $this->splitDomainAndPort($raw_domain, $version);

        try {

            /** @var NginxHost $host */
            $host = $this->hostFactory->get($version);

            /** @var NginxStore $store */
            $store = $this->storeFactory->get($version);
            if (! $host->exists($domain, $port)) {
                throw new HostException('Domain not found', 404);
            }

            $host->delete($domain, $port);
            $store->delete($domain, $port);

            $this->commandService->execute('reload');

        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['domain' => $domain, 'port' => $port]);
            throw $e;
        }
    }
}
