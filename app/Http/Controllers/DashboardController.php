<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function alerts(Request $request)
    {
        $doctor = $request->user()->doctor;

        if (!$doctor) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $latePatients = Patient::where('doctor_id', $doctor->id)
            ->where('active', true)
            ->whereDoesntHave('diarySessions', function ($q) {
                $q->where('session_date', '>=', Carbon::now()->subDays(7));
            })
            ->whereHas('treatments', function ($q) {
                $q->where('status', 'ongoing');
            })
            ->with('user')
            ->get()
            ->map(function ($patient) {
                $latestSession = $patient->diarySessions()
                    ->orderBy('session_date', 'desc')
                    ->first();

                return [
                    'id' => $patient->id,
                    'name' => $patient->user?->name,
                    'days_since_last_session' => $latestSession
                        ? Carbon::parse($latestSession->session_date)->diffInDays(Carbon::now())
                        : null,
                    'last_session_date' => $latestSession?->session_date,
                ];
            });

        $totalActive = Patient::where('doctor_id', $doctor->id)
            ->where('active', true)
            ->whereHas('treatments', fn($q) => $q->where('status', 'ongoing'))
            ->count();

        return response()->json([
            'late_patients' => $latePatients,
            'total_late' => $latePatients->count(),
            'total_active_in_treatment' => $totalActive,
            'compliance_rate' => $totalActive > 0
                ? round((($totalActive - $latePatients->count()) / $totalActive) * 100, 1)
                : 100,
        ]);
    }
}
