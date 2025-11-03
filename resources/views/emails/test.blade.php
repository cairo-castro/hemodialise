<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teste de Email</title>
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
        .success-icon {
            font-size: 48px;
            margin: 20px 0;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
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
        <h1>✅ Sistema Hemodiálise</h1>
        <p>Configuração de Email</p>
    </div>

    <div class="content">
        <div class="success-icon">🎉</div>

        <h2>Email Configurado com Sucesso!</h2>

        <p>Parabéns! O sistema de envio de emails foi configurado corretamente.</p>

        <div class="info-box">
            <h3>Informações da Configuração:</h3>
            <ul>
                <li><strong>Servidor SMTP:</strong> Gmail (smtp.gmail.com)</li>
                <li><strong>Porta:</strong> 587</li>
                <li><strong>Encryption:</strong> TLS</li>
                <li><strong>Email:</strong> emserhq@gmail.com</li>
            </ul>
        </div>

        <p>Agora o sistema pode enviar:</p>
        <ul>
            <li>Notificações de checklist</li>
            <li>Alertas de manutenção</li>
            <li>Relatórios semanais</li>
            <li>Atualizações do sistema</li>
            <li>Recuperação de senha</li>
        </ul>

        <p><strong>Data do teste:</strong> {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="footer">
        <p>Sistema de Gestão de Hemodiálise - Qualidade</p>
        <p>Este é um email automático, por favor não responda.</p>
    </div>
</body>
</html>
