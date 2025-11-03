<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Alerta de Manutenção</title>
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
            background: linear-gradient(135deg, #f59e0b 0%, #dc2626 100%);
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
        .alert-box {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
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
            background: #f59e0b;
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
        <h1>⚠️ Alerta de Manutenção</h1>
        <p>Manutenção preventiva programada</p>
    </div>

    <div class="content">
        <div class="alert-box">
            <h3>⏰ Atenção Necessária</h3>
            <p>Uma manutenção preventiva está próxima do vencimento e requer sua atenção.</p>
        </div>

        <div class="info-box">
            <h3>Detalhes da Manutenção:</h3>
            <div class="info-row">
                <span class="label">Máquina:</span>
                <span class="value">{{ $machine->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Número de Série:</span>
                <span class="value">{{ $machine->serial_number }}</span>
            </div>
            <div class="info-row">
                <span class="label">Tipo de Manutenção:</span>
                <span class="value">{{ $maintenanceType }}</span>
            </div>
            <div class="info-row">
                <span class="label">Data Prevista:</span>
                <span class="value">{{ $dueDate }}</span>
            </div>
            <div class="info-row">
                <span class="label">Unidade:</span>
                <span class="value">{{ $machine->unit->name ?? 'N/A' }}</span>
            </div>
        </div>

        <p><strong>Ação Requerida:</strong> Por favor, programe e realize a manutenção preventiva o mais breve possível para garantir o funcionamento seguro do equipamento.</p>

        <a href="{{ config('app.url') }}" class="button">Acessar Sistema</a>
    </div>

    <div class="footer">
        <p>Sistema de Gestão de Hemodiálise - Qualidade</p>
        <p>Este é um email automático, por favor não responda.</p>
    </div>
</body>
</html>
