<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Semanal</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #10b981;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #10b981;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Relatório Semanal</h1>
        <p>{{ $reportData['period'] ?? 'Resumo da Semana' }}</p>
    </div>

    <div class="content">
        <p>Aqui está o resumo das atividades da última semana:</p>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $reportData['total_checklists'] ?? 0 }}</div>
                <div class="stat-label">Checklists Realizados</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $reportData['total_cleanings'] ?? 0 }}</div>
                <div class="stat-label">Limpezas Realizadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $reportData['active_machines'] ?? 0 }}</div>
                <div class="stat-label">Máquinas Ativas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $reportData['total_sessions'] ?? 0 }}</div>
                <div class="stat-label">Sessões de Diálise</div>
            </div>
        </div>

        @if(isset($reportData['highlights']) && count($reportData['highlights']) > 0)
        <div class="info-box">
            <h3>📌 Destaques da Semana:</h3>
            <ul>
                @foreach($reportData['highlights'] as $highlight)
                <li>{{ $highlight }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(isset($reportData['alerts']) && count($reportData['alerts']) > 0)
        <div class="info-box" style="border-left: 4px solid #f59e0b;">
            <h3>⚠️ Atenção Necessária:</h3>
            <ul>
                @foreach($reportData['alerts'] as $alert)
                <li>{{ $alert }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <p>Acesse o sistema para visualizar relatórios detalhados e análises completas.</p>

        <a href="{{ config('app.url') }}" class="button">Acessar Sistema</a>
    </div>

    <div class="footer">
        <p>Sistema de Gestão de Hemodiálise - Qualidade</p>
        <p>Este é um email automático, por favor não responda.</p>
    </div>
</body>
</html>
