<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Progresso - {{ $patient->user->name }}</title>
    <style>
        @page {
            margin: 25mm 15mm 25mm 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #1565C0;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            color: #1565C0;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 10px;
            color: #666;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1565C0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 180px;
            padding: 3px 8px 3px 0;
            color: #555;
        }

        .info-value {
            display: table-cell;
            padding: 3px 0;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }

        .stat-card {
            display: inline-block;
            width: 30%;
            text-align: center;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            margin-right: 3%;
        }

        .stat-card:last-child {
            margin-right: 0;
        }

        .stat-value {
            font-size: 22px;
            font-weight: bold;
            color: #1565C0;
        }

        .stat-label {
            font-size: 9px;
            color: #888;
            text-transform: uppercase;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        table.data th {
            background: #1565C0;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
        }

        table.data td {
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
        }

        table.data tr:nth-child(even) td {
            background: #f8f9fa;
        }

        .chart-container {
            text-align: center;
            margin: 15px 0;
        }

        .chart-container svg {
            max-width: 100%;
        }

        .legend {
            text-align: center;
            font-size: 9px;
            margin-top: 8px;
        }

        .legend span {
            display: inline-block;
            margin: 0 12px;
        }

        .legend-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 4px;
        }

        .footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 4px;
        }

        .badge {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-ongoing {
            background: #e3f2fd;
            color: #1565C0;
        }

        .badge-completed {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-cancelled {
            background: #fbe9e7;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Progresso</h1>
        <p>Rehably — Plataforma de Telerreabilitação</p>
        <p>Gerado em {{ $generatedAt }}</p>
    </div>

    <div class="section">
        <div class="section-title">Dados do Paciente</div>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Nome</span>
                <span class="info-value">{{ $patient->user->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">E-mail</span>
                <span class="info-value">{{ $patient->user->email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Telefone</span>
                <span class="info-value">{{ $patient->user->phone_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Data de Nascimento</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($patient->birth_date)->format('d/m/Y') }}</span>
            </div>
            @if($patient->clinical_condition)
            <div class="info-row">
                <span class="info-label">Condição Clínica</span>
                <span class="info-value">{{ $patient->clinical_condition }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Histórico de Tratamentos</div>
        <table class="data">
            <thead>
                <tr>
                    <th>Tratamento</th>
                    <th>Período</th>
                    <th>Exercícios</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($treatments as $t)
                <tr>
                    <td>{{ $t['title'] }}</td>
                    <td>{{ $t['start_date'] }} — {{ $t['end_date'] }}</td>
                    <td>{{ $t['items_count'] }}</td>
                    <td><span class="badge badge-{{ $t['status'] === 'ongoing' ? 'ongoing' : ($t['status'] === 'completed' ? 'completed' : 'cancelled') }}">{{ $t['status'] === 'ongoing' ? 'Em andamento' : ($t['status'] === 'completed' ? 'Concluído' : 'Cancelado') }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:#999;">Nenhum tratamento registrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Resumo de Adesão ({{ $startDate }} — {{ $endDate }})</div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $totalSessions }}</div>
                <div class="stat-label">Total de Sessões</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $completedSessions }}</div>
                <div class="stat-label">Concluídas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $adherenceRate }}%</div>
                <div class="stat-label">Taxa de Adesão</div>
            </div>
        </div>

        @if($avgPain !== null || $avgFatigue !== null || $avgDifficulty !== null)
        <table class="data" style="margin-top:15px;">
            <thead>
                <tr>
                    <th>Métrica</th>
                    <th>Média no Período (escala 1–5)</th>
                </tr>
            </thead>
            <tbody>
                @if($avgPain !== null)
                <tr>
                    <td>Dor</td>
                    <td>{{ number_format($avgPain, 1) }}</td>
                </tr>
                @endif
                @if($avgFatigue !== null)
                <tr>
                    <td>Fadiga</td>
                    <td>{{ number_format($avgFatigue, 1) }}</td>
                </tr>
                @endif
                @if($avgDifficulty !== null)
                <tr>
                    <td>Dificuldade de Execução</td>
                    <td>{{ number_format($avgDifficulty, 1) }}</td>
                </tr>
                @endif
            </tbody>
        </table>
        @endif
    </div>

    @if($dailyData->count() > 1)
    <div class="section">
        <div class="section-title">Evolução Diária</div>
        <div class="chart-container">
            @php
                $points = $dailyData;
                $width = 600;
                $height = 200;
                $padding = 40;
                $chartW = $width - 2 * $padding;
                $chartH = $height - 2 * $padding;
                $count = $points->count();
                $stepX = $count > 1 ? $chartW / ($count - 1) : $chartW;

                $maxVal = 5;
                $series = [
                    'pain' => ['color' => '#ef4444', 'label' => 'Dor', 'data' => $points->pluck('avg_pain')->map(fn($v) => $v ?? 0)->toArray()],
                    'fatigue' => ['color' => '#f59e0b', 'label' => 'Fadiga', 'data' => $points->pluck('avg_fatigue')->map(fn($v) => $v ?? 0)->toArray()],
                    'difficulty' => ['color' => '#3b82f6', 'label' => 'Dificuldade', 'data' => $points->pluck('avg_difficulty')->map(fn($v) => $v ?? 0)->toArray()],
                ];
            @endphp
            <svg width="{{ $width }}" height="{{ $height }}" viewBox="0 0 {{ $width }} {{ $height }}">
                <rect x="0" y="0" width="{{ $width }}" height="{{ $height }}" fill="white"/>
                @for($i = 0; $i <= 5; $i++)
                    <line x1="{{ $padding }}" y1="{{ $padding + ($chartH - ($i / $maxVal) * $chartH) }}" x2="{{ $width - $padding }}" y2="{{ $padding + ($chartH - ($i / $maxVal) * $chartH) }}" stroke="#f0f0f0" stroke-width="1"/>
                    <text x="{{ $padding - 5 }}" y="{{ $padding + ($chartH - ($i / $maxVal) * $chartH) + 3 }}" text-anchor="end" font-size="8" fill="#999">{{ $i }}</text>
                @endfor
                @foreach($series as $s)
                    @php
                        $vals = $s['data'];
                        $pointsStr = '';
                        $hasValue = false;
                        foreach($vals as $idx => $v) {
                            if ($v > 0) $hasValue = true;
                            $x = $padding + $idx * $stepX;
                            $y = $padding + ($chartH - ($v / $maxVal) * $chartH);
                            $pointsStr .= ($idx > 0 ? ' ' : '') . $x . ',' . $y;
                        }
                    @endphp
                    @if($hasValue)
                    <polyline points="{{ $pointsStr }}" fill="none" stroke="{{ $s['color'] }}" stroke-width="2" stroke-linejoin="round"/>
                    @endif
                @endforeach
                @if($count > 1)
                    @foreach($points as $idx => $day)
                        @php $x = $padding + $idx * $stepX; @endphp
                        @if($idx % max(1, intdiv($count, 8)) == 0 || $idx == $count - 1)
                            <text x="{{ $x }}" y="{{ $height - $padding + 12 }}" text-anchor="middle" font-size="7" fill="#999" transform="rotate(-30, {{ $x }}, {{ $height - $padding + 12 }})">{{ \Carbon\Carbon::parse($day['date'])->format('d/m') }}</text>
                        @endif
                    @endforeach
                @endif
                <text x="{{ $padding }}" y="{{ $height - 5 }}" font-size="8" fill="#666">Data</text>
                <text x="5" y="{{ $padding - 8 }}" font-size="8" fill="#666">Escala</text>
            </svg>
        </div>
        <div class="legend">
            @foreach($series as $s)
            <span><span class="legend-dot" style="background:{{ $s['color'] }}"></span>{{ $s['label'] }}</span>
            @endforeach
        </div>
    </div>
    @endif

    <div class="footer">
        Rehably — Relatório gerado automaticamente em {{ $generatedAt }} | Página {PAGE_NUM} de {PAGE_COUNT}
    </div>
</body>
</html>
