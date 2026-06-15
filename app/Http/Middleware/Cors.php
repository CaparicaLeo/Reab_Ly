<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::debug('CORS middleware running', [
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'origin' => $request->headers->get('Origin'),
        ]);

        if ($request->isMethod('OPTIONS')) {
            Log::debug('CORS middleware handling OPTIONS');
            return response('', 204, $this->headers());
        }

        $response = $next($request);

        foreach ($this->headers() as $key => $value) {
            $response->headers->set($key, $value);
        }

        Log::debug('CORS middleware completed', [
            'has_header' => $response->headers->has('Access-Control-Allow-Origin'),
            'header_value' => $response->headers->get('Access-Control-Allow-Origin'),
        ]);

        return $response;
    }

    private function headers(): array
    {
        return [
            'Access-Control-Allow-Origin' => 'http://localhost:5174',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, Origin',
            'Access-Control-Allow-Credentials' => 'true',
        ];
    }
}
