<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Http;

class TokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        $token = $request->bearerToken();

        if (empty($token) || ! $route instanceof Route) {
            return response()->json([], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $response = Http::withToken($token)->post(
            env('TOKEN_VALIDATION_SERVICE'),
            [
                'force_platform_keycloak' => $this->isAdminRoute($route),
                'permission_name' => env('APP_NAME').'#'.$route->getName(),
            ]
        );

        if ($response->status() === JsonResponse::HTTP_OK) {
            return $next($request);
        }

        return response()->json(json_decode($response->body(), true), $response->status());
    }

    private function isAdminRoute(Route $route): bool
    {
        return (bool) preg_match('#\/admin#', $route->uri());
    }
}
