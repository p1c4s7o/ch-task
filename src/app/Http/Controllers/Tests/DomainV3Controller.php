<?php

namespace App\Http\Controllers\Tests;

use App\Actions\Domain\CreateDomainAction;
use App\Actions\Domain\GetDomainStatusAction;
use App\Domain\Nginx\Exceptions\HostException;
use App\Http\Controllers\DomainController;
use App\Http\Requests\CreateDomainRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

final class DomainV3Controller extends DomainController
{
    /**
     * @return JsonResponse|Response
     *
     * @throws HostException
     * @throws Throwable
     */
    public function status(int $version, string $rawDomain, Request $request, GetDomainStatusAction $action)
    {
        if ($version === 3) {
            sleep($version);
            if ($request->method() === 'HEAD') {
                return response()->noContent(200);
            }

            return response()->json([
                'status' => false,
                'message' => 'Wow! Domain creation is in progress...',
            ]);
        }

        return parent::status($version, $rawDomain, $request, $action);
    }

    /**
     * @return JsonResponse
     */
    public function create(int $version, CreateDomainRequest $request, CreateDomainAction $action)
    {
        if ($version === 3) {
            sleep($version);

            return response()->json([
                'status' => false,
                'message' => 'Hold on! Domain may be created soon, please wait... ',
            ], 423);
        }

        return parent::create($version, $request, $action);
    }
}
