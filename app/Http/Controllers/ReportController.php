<?php

namespace App\Http\Controllers;

use App\Models\DiarySession;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function generate(Request $request, Patient $patient)
    {
        $doctor = $request->user()->doctor;

        if (!$doctor || $patient->doctor_id !== $doctor->id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $patient->load('user');

        $startDate = $request->query('start_date', $patient->treatments()->min('start_date') ?? Carbon::now()->subMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        $sessions = DiarySession::where('patient_id', $patient->id)
            ->whereBetween('session_date', [$startDate, $endDate])
            ->with('treatmentItem.exercise')
            ->orderBy('session_date')
            ->get();

        $total = $sessions->count();
        $completed = $sessions->where('completed', true)->count();
        $adherenceRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        $avgPain = $completed > 0 ? round($sessions->whereNotNull('pain_level')->avg('pain_level'), 1) : null;
        $avgFatigue = $completed > 0 ? round($sessions->whereNotNull('fatigue_level')->avg('fatigue_level'), 1) : null;
        $avgDifficulty = $completed > 0 ? round($sessions->whereNotNull('difficulty_level')->avg('difficulty_level'), 1) : null;

        $dailyData = $sessions->groupBy('session_date')->map(function ($day) {
            $dayCompleted = $day->where('completed', true);
            return [
                'date' => $day->first()->session_date,
                'total' => $day->count(),
                'completed' => $dayCompleted->count(),
                'avg_pain' => $dayCompleted->whereNotNull('pain_level')->avg('pain_level'),
                'avg_fatigue' => $dayCompleted->whereNotNull('fatigue_level')->avg('fatigue_level'),
                'avg_difficulty' => $dayCompleted->whereNotNull('difficulty_level')->avg('difficulty_level'),
            ];
        })->values();

        $treatments = $patient->treatments()
            ->orderBy('start_date')
            ->get()
            ->map(fn($t) => [
                'title' => $t->title,
                'status' => $t->status,
                'start_date' => $t->start_date?->format('d/m/Y'),
                'end_date' => $t->end_date?->format('d/m/Y') ?? 'Em andamento',
                'items_count' => $t->items()->count(),
            ]);

        $pdf = Pdf::loadView('reports.patient_progress', [
            'patient' => $patient,
            'startDate' => $startDate instanceof Carbon ? $startDate->format('d/m/Y') : Carbon::parse($startDate)->format('d/m/Y'),
            'endDate' => $endDate instanceof Carbon ? $endDate->format('d/m/Y') : Carbon::parse($endDate)->format('d/m/Y'),
            'totalSessions' => $total,
            'completedSessions' => $completed,
            'adherenceRate' => $adherenceRate,
            'avgPain' => $avgPain,
            'avgFatigue' => $avgFatigue,
            'avgDifficulty' => $avgDifficulty,
            'dailyData' => $dailyData,
            'treatments' => $treatments,
            'generatedAt' => Carbon::now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('a4');
        $pdf->setOptions([
            'margin_top' => 20,
            'margin_bottom' => 20,
            'margin_left' => 15,
            'margin_right' => 15,
        ]);

        return $pdf->download('relatorio-progresso-paciente.pdf');
    }
}
