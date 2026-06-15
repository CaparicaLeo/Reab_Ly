<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLgpdConsent
{
    public function handle(Request $request, Closure $next): Response
    {
        $patient = $request->user()?->patient;

        if ($patient && !$patient->consentimento_lgpd) {
            return response()->json([
                'message' => 'Consentimento LGPD necessário. Acesse POST /api/consent para aceitar os termos.',
                'requires_consent' => true,
            ], 403);
        }

        return $next($request);
    }
}
