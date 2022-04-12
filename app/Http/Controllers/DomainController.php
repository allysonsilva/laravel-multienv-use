<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DomainController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(config('domain'));
    }

    public function domainFilename(): JsonResponse
    {
        return response()->json(getenv('DOMAIN_ENV_FILENAME'));
    }

    public function cachedRoutes(): JsonResponse
    {
        return response()->json([
            'routesAreCached' => app()->routesAreCached(),
            'getCachedRoutesPath' => app()->getCachedRoutesPath(),
        ]);
    }

    public function cachedConfig(): JsonResponse
    {
        return response()->json([
            'configurationIsCached' => app()->configurationIsCached(),
            'getCachedConfigPath' => app()->getCachedConfigPath(),
        ]);
    }
}
