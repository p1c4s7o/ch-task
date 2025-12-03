<?php

namespace App\Http\Controllers;

use App\Actions\Domain\CreateDomainAction;
use App\Actions\Domain\DeleteDomainAction;
use App\Actions\Domain\GetDomainStatusAction;
use App\Domain\Nginx\Enums\NginxApiVersion;
use App\Domain\Nginx\Exceptions\HostException;
use App\Domain\Nginx\Exceptions\IOError;
use App\Helpers\ApiResponse;
use App\Http\Requests\CreateDomainRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 *
 * @OA\Info(
 *         title="CityHostTask",
 *         version="1.0.0",
 *         description="API documentation"
 * )
 *
 *
 * @OA\Server(
 *         url="http://127.0.0.1:8000",
 *         description="Simple API Server"
 * )
 * @OA\Server(
 *         url=L5_SWAGGER_CONST_HOST,
 *         description="API Server (env: L5_SWAGGER_CONST_HOST)"
 * )
 *
 */
class DomainController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/v{version}/domain/create",
     *      summary="Create domain",
     *      tags={"Domain"},
     *
     *      @OA\Parameter(
     *          name="version",
     *          in="path",
     *          required=true,
     *          description="API version",
     *
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              type="object",
     *              required={"domain"},
     *
     *              @OA\Property(property="domain", type="string", example="test")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Domain creation has been queued",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="status", type="string", example="created"),
     *              @OA\Property(property="domain", type="string", example="test")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Domain successfully created",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="status", type="string", example="created"),
     *              @OA\Property(property="domain", type="string", example="test")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=202,
     *          description="Domain creation queued",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="status", type="string", example="created"),
     *              @OA\Property(property="domain", type="string", example="test")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=409,
     *          description="Domain already exists",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="error", type="string", example="Domain already exists"),
     *              @OA\Property(property="domain", type="string", example="test")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=423,
     *          description="Domain creation is already in progress",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="error", type="string", example="Domain is being created"),
     *              @OA\Property(property="domain", type="string", example="test")
     *          )
     *      ),
     *
     *      @OA\Response(response=400, description="Invalid input"),
     *      @OA\Response(response=415, description="Malformed JSON structure"),
     *      @OA\Response(response=500, description="Server error")
     *  )
     *
     * @return JsonResponse
     */
    public function create(int $version, CreateDomainRequest $request, CreateDomainAction $action)
    {
        $api_version = $this->validateApiVersion($version);
        $domain = $request->json('domain');
        $port = $request->json('port');

        $result = $action->handle($api_version, $domain, $port);

        if ($result->exists) {
            return ApiResponse::error(
                'Domain already exists',
                409,
            );
        }

        if ($result->isQueued()) {
            return ApiResponse::success(
                ['link' => $result->link],
                202,
                'Domain creation queued'
            );
        }

        if ($result->isCreated()) {
            return ApiResponse::success(
                ['link' => $result->link],
                201,
                'Domain created'
            );
        }

        return ApiResponse::error(
            $result->message,
            500
        );
    }

    /**
     * @OA\Delete(
     *      path="/api/v{version}/domain/{domain}",
     *      summary="Delete domain",
     *      tags={"Domain"},
     *
     *      @OA\Parameter(
     *          name="version",
     *          in="path",
     *          required=true,
     *          description="API version",
     *
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *
     *      @OA\Parameter(
     *          name="domain",
     *          in="path",
     *          required=true,
     *          description="Domain to delete",
     *
     *          @OA\Schema(type="string", example="test")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Domain deleted"
     *      ),
     *      @OA\Response(response=404, description="Domain not found"),
     *      @OA\Response(response=500, description="Server error")
     *  )
     *
     * @return JsonResponse
     *
     * @throws HostException
     * @throws IOError
     * @throws \Throwable
     */
    public function delete(int $version, string $raw_domain, DeleteDomainAction $action)
    {
        $api_version = $this->validateApiVersion($version);
        try {
            $action->handle($api_version, $raw_domain);
        } catch (HostException $e) {
            if ($e->getCode() === 404) {
                return ApiResponse::error($e->getMessage(), 404);
            }
            throw $e;
        }

        return ApiResponse::success([], 200, 'Domain deleted');
    }

    /**
     * @OA\Get(
     *      path="/api/v{version}/status/{domain}",
     *      summary="Check domain status",
     *      tags={"Domain"},
     *
     *      @OA\Parameter(
     *          name="version",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *
     *      @OA\Parameter(
     *          name="domain",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(type="string", example="test")
     *      ),
     *
     *      @OA\Response(response=200, description="Domain status"),
     *      @OA\Response(response=404, description="Domain not found"),
     *      @OA\Response(response=500, description="Server error")
     *  )
     *
     * @OA\Head(
     *      path="/api/v{version}/status/{domain}",
     *      summary="Check domain status (HEAD)",
     *      tags={"Domain"},
     *
     *      @OA\Parameter(
     *          name="version",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *
     *      @OA\Parameter(
     *          name="domain",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(type="string", example="test")
     *      ),
     *
     *      @OA\Response(response=200, description="Domain status"),
     *      @OA\Response(response=403, description="Request contains invalid or missing parameters"),
     *      @OA\Response(response=404, description="Domain not found"),
     *      @OA\Response(response=500, description="Server error")
     *  )
     *
     * @return JsonResponse
     *
     * @throws HostException
     * @throws \Throwable
     */
    public function status(int $version, string $rawDomain, Request $request, GetDomainStatusAction $action)
    {
        $api_version = $this->validateApiVersion($version, $request->method() === 'HEAD');
        $status = $action->handle($api_version, $rawDomain);
        if ($status->exists) {
            return ApiResponse::success([], 200, 'Domain found');
        }

        return ApiResponse::error('Domain not found', 404);
    }

    protected function validateApiVersion(int $version, bool $isHead = false): ?NginxApiVersion
    {
        $enum = NginxApiVersion::tryFrom('v'.$version);
        if (! $enum) {
            abort($isHead ? 403 : 404, $isHead ? '' : 'API version not supported');
        }

        return $enum;
    }
}
