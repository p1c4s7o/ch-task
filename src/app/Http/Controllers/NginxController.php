<?php

namespace App\Http\Controllers;

use App\Contracts\Nginx\CommandServiceInterface;

class NginxController extends Controller
{
    public function __construct(private readonly CommandServiceInterface $command) {}

    /**
     * @OA\Post(
     *     path="/api/server/stop",
     *     summary="Stop Nginx server",
     *     tags={"Nginx"},
     *
     *     @OA\Response(response=200, description="Server stopped"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function stop(): mixed
    {
        // TODO Redis check
        $cmd = $this->command->execute(__FUNCTION__);

        return response()->json(['status' => $cmd->success, 'message' => $cmd->output, 'error' => $cmd->error], $cmd->success ? 200 : 500);
    }

    /**
     * @OA\Post(
     *     path="/api/server/start",
     *     summary="Start Nginx server",
     *     tags={"Nginx"},
     *
     *     @OA\Response(response=200, description="Server started"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function start(): mixed
    {
        $cmd = $this->command->execute(__FUNCTION__);

        return response()->json(['status' => $cmd->success, 'message' => $cmd->output, 'error' => $cmd->error], $cmd->success ? 200 : 500);
    }

    /**
     * @OA\Post(
     *     path="/api/server/reload",
     *     summary="Reload Nginx server",
     *     tags={"Nginx"},
     *
     *     @OA\Response(response=200, description="Server reloaded"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function reload(): mixed
    {
        $cmd = $this->command->execute(__FUNCTION__);

        return response()->json(['status' => $cmd->success, 'message' => $cmd->output, 'error' => $cmd->error], $cmd->success ? 200 : 500);
    }

    /**
     * @OA\Post (
     *     path="/api/server/restart",
     *     summary="Restart Nginx server",
     *     tags={"Nginx"},
     *
     *     @OA\Response(response=200, description="Server restarted"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function restart(): mixed
    {
        $cmd = $this->command->execute(__FUNCTION__);

        return response()->json(['status' => $cmd->success, 'message' => $cmd->output, 'error' => $cmd->error], $cmd->success ? 200 : 500);
    }

    /**
     * @OA\Get (
     *     path="/api/server/status",
     *     summary="Get Nginx server status",
     *     tags={"Nginx"},
     *
     *     @OA\Response(response=200, description="Current server status"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function status(): mixed
    {
        $cmd = $this->command->execute(__FUNCTION__);

        return response()->json(['status' => $cmd->success, 'message' => $cmd->output, 'error' => $cmd->error], $cmd->success ? 200 : 500);
    }
}
