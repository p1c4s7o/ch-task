<?php

namespace App\Actions\Domain;

use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Exceptions\HostException;
use App\Helpers\ApiResult;
use App\Services\Nginx\NginxHost;
use App\Services\Nginx\NginxStore;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetDomainStatusAction extends BaseAction
{
    /**
     * @throws HostException
     * @throws Throwable
     */
    public function handle(NginxApiVersion $version, string $raw_domain): ApiResult
    {
        [$domain, $port] = $this->splitDomainAndPort($raw_domain, $version);

        try {

            /** @var NginxHost $host */
            $host = $this->hostFactory->get($version);

            /** @var NginxStore $store */
            $store = $this->storeFactory->get($version);
            if (! ($host->exists($domain, $port) && $store->exists($domain, $port))) {
                return ApiResult::error('Domain not found', 404);
            }

        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['domain' => $domain, 'port' => $port]);
            throw $e;
        }

        return ApiResult::exists($this->buildLink($version, $domain, $port));
    }
}
