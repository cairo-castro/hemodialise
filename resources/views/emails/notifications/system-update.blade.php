<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Atualização do Sistema</title>
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
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
        .update-badge {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 10px;
            margin: 8px 0;
            background: #f0f9ff;
            border-radius: 6px;
            padding-left: 35px;
            position: relative;
        }
        .feature-list li:before {
            content: "✨";
            position: absolute;
            left: 10px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #3b82f6;
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
        <h1>🚀 Atualização do Sistema</h1>
        <p>Novidades e melhorias disponíveis</p>
    </div>

    <div class="content">
        <span class="update-badge">NOVA ATUALIZAÇÃO</span>

        <h2>{{ $updateTitle }}</h2>

        <p>{{ $updateDescription }}</p>

        @if(count($features) > 0)
        <div class="info-box">
            <h3>O que há de novo:</h3>
            <ul class="feature-list">
                @foreach($features as $feature)
                <li>{{ $feature }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="info-box">
            <p><strong>📅 Data da atualização:</strong> {{ date('d/m/Y H:i') }}</p>
            <p>Todas as melhorias já estão disponíveis no sistema. Não é necessária nenhuma ação da sua parte.</p>
        </div>

        <p>Faça login no sistema para experimentar as novidades!</p>

        <a href="{{ config('app.url') }}" class="button">Acessar Sistema</a>
    </div>

    <div class="footer">
        <p>Sistema de Gestão de Hemodiálise - Qualidade</p>
        <p>Este é um email automático, por favor não responda.</p>
    </div>
</body>
</html>
