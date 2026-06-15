<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $patient = $request->user()->patient;

        if (!$patient) {
            return response()->json(['message' => 'Apenas pacientes podem registrar consentimento.'], 403);
        }

        if ($patient->consentimento_lgpd) {
            return response()->json([
                'message' => 'Consentimento já registrado anteriormente.',
                'consentimento_em' => $patient->consentimento_em,
            ]);
        }

        $patient->update([
            'consentimento_lgpd' => true,
            'consentimento_em' => now(),
        ]);

        return response()->json([
            'message' => 'Consentimento registrado com sucesso.',
            'consentimento_em' => $patient->fresh()->consentimento_em,
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        $patient = $request->user()->patient;

        if (!$patient) {
            return response()->json(['message' => 'Apenas pacientes podem consultar o consentimento.'], 403);
        }

        return response()->json([
            'consentimento_lgpd' => $patient->consentimento_lgpd,
            'consentimento_em' => $patient->consentimento_em,
        ]);
    }
}
