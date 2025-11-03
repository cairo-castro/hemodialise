<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Novo Checklist de Segurança</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .value {
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
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
        <h1>🔔 Novo Checklist de Segurança</h1>
        <p>Um novo checklist foi criado no sistema</p>
    </div>

    <div class="content">
        <p>Um novo checklist de segurança foi registrado e requer atenção.</p>

        <div class="info-box">
            <h3>Detalhes do Checklist:</h3>
            <div class="info-row">
                <span class="label">Paciente:</span>
                <span class="value">{{ $checklist->patient->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Máquina:</span>
                <span class="value">{{ $checklist->machine->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Turno:</span>
                <span class="value">{{ ucfirst($checklist->shift) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="value">{{ $checklist->status === 'completed' ? 'Completo' : 'Em andamento' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Criado por:</span>
                <span class="value">{{ $checklist->user->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Data:</span>
                <span class="value">{{ $checklist->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <p>Acesse o sistema para visualizar todos os detalhes do checklist.</p>

        <a href="{{ config('app.url') }}" class="button">Acessar Sistema</a>
    </div>

    <div class="footer">
        <p>Sistema de Gestão de Hemodiálise - Qualidade</p>
        <p>Este é um email automático, por favor não responda.</p>
    </div>
</body>
</html>
