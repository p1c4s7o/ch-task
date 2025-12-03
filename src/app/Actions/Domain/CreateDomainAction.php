<?php

namespace App\Actions\Domain;

use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Exceptions\HostException;
use App\Domain\Nginx\Exceptions\IOError;
use App\Exceptions\NginxRedisException;
use App\Helpers\ApiResult;
use App\Services\Nginx\NginxHost;
use App\Services\Nginx\NginxStore;
use Illuminate\Support\Facades\Log;

class CreateDomainAction extends BaseAction
{
    public function handle(NginxApiVersion $version, string $domain, ?int $port = null): ApiResult
    {
        try {
            /** @var NginxHost $host */
            $host = $this->hostFactory->get($version);
            if ($host->exists($domain, $port)) {
                return ApiResult::exists($this->buildLink($version, $domain, $port));
            }

            if (! $host->create($domain, $port)) {
                return ApiResult::error('Domain creation failed');
            }

            /** @var NginxStore $store */
            $store = $this->storeFactory->get($version);
            if (! $store->createIndexFile($domain, $port)) {
                return ApiResult::error('Index file creation failed.');
            }

            $cmd = $this->commandService->execute('test');
            if (! $cmd->success) {
                Log::error('nginx test failed', ['error' => $cmd->error, 'output' => $cmd->output]);

                $host->delete($domain, $port);
                $store->delete($domain, $port);

                return ApiResult::error('Nginx config test failed');
            }

            try {

                $cmd = $this->commandService->execute('reload');
                if (! $cmd->success) {
                    Log::error('nginx reload failed', ['error' => $cmd->error, 'output' => $cmd->output]);

                    return ApiResult::error('Nginx reload failed');
                }

            } catch (NginxRedisException $e) {
                return ApiResult::queued($this->buildLink($version, $domain, $port));
            }

            return ApiResult::created($this->buildLink($version, $domain, $port));
        } catch (HostException $e) {
            Log::error($e->getMessage(), ['domain' => $domain, 'port' => $port]);

            return ApiResult::error($e->getMessage());
        } catch (IOError $e) {
            Log::error($e->getMessage(), ['domain' => $domain, 'port' => $port]);

            return ApiResult::error('I/O Error');
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return ApiResult::error('Internal Server Error');
        }
    }
}
