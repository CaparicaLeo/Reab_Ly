<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiarySessionRequest;
use App\Models\DiarySession;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class DiarySessionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', DiarySession::class);

        $doctor = $request->user()->doctor()->first();
        $patient = $request->user()->patient()->first();

        $query = DiarySession::with('treatmentItem.exercise');

        if ($doctor) {
            $patientId = $request->query('patient_id');
            if (!$patientId) {
                return response()->json(['message' => 'O parâmetro patient_id é obrigatório para médicos.'], 422);
            }
            $query->whereHas('patient', fn($q) => $q->where('doctor_id', $doctor->id)->where('id', $patientId));
        } elseif ($patient) {
            $query->where('patient_id', $patient->id);
        }

        if ($request->has('date')) {
            $query->where('session_date', $request->query('date'));
        }

        if ($request->has('start_date')) {
            $query->where('session_date', '>=', $request->query('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('session_date', '<=', $request->query('end_date'));
        }

        $perPage = min((int) $request->query('per_page', 30), 100);
        $sessions = $query->orderBy('session_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($sessions);
    }

    public function store(StoreDiarySessionRequest $request)
    {
        $this->authorize('create', DiarySession::class);

        $patient = $request->user()->patient;

        $session = DiarySession::create([
            'patient_id' => $patient->id,
            'treatment_item_id' => $request->treatment_item_id,
            'session_date' => $request->session_date ?? Carbon::today()->toDateString(),
            'completed' => true,
            'pain_level' => $request->pain_level,
            'fatigue_level' => $request->fatigue_level,
            'difficulty_level' => $request->difficulty_level,
        ]);

        $session->load('treatmentItem.exercise');

        return response()->json($session, 201);
    }

    public function show(DiarySession $diarySession)
    {
        $this->authorize('view', $diarySession);

        $diarySession->load('treatmentItem.exercise');

        return response()->json($diarySession);
    }

    public function stats(Request $request)
    {
        $this->authorize('viewAny', DiarySession::class);

        $doctor = $request->user()->doctor()->first();
        $patient = $request->user()->patient()->first();

        $patientId = $request->query('patient_id');

        if ($doctor) {
            if (!$patientId) {
                return response()->json(['message' => 'O parâmetro patient_id é obrigatório para médicos.'], 422);
            }
            $patientModel = Patient::where('id', $patientId)->where('doctor_id', $doctor->id)->firstOrFail();
        } elseif ($patient) {
            $patientModel = $patient;
        } else {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $query = DiarySession::where('patient_id', $patientModel->id);

        $startDate = $request->query('start_date', Carbon::now()->subMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());

        $query->whereBetween('session_date', [$startDate, $endDate]);

        $sessions = $query->get();
        $total = $sessions->count();
        $completed = $sessions->where('completed', true)->count();

        $stats = [
            'total_sessions' => $total,
            'completed_sessions' => $completed,
            'adherence_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'avg_pain' => $completed > 0 ? round($sessions->whereNotNull('pain_level')->avg('pain_level'), 1) : null,
            'avg_fatigue' => $completed > 0 ? round($sessions->whereNotNull('fatigue_level')->avg('fatigue_level'), 1) : null,
            'avg_difficulty' => $completed > 0 ? round($sessions->whereNotNull('difficulty_level')->avg('difficulty_level'), 1) : null,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];

        return response()->json($stats);
    }
}
