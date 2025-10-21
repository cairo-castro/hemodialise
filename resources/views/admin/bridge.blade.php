<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Redirecionando...</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .loader {
            text-align: center;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #ddd;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <p>Redirecionando para o painel administrativo...</p>
    </div>

    <script>
        let retryCount = 0;
        const MAX_RETRIES = 2;

        function attemptAdminLogin() {
            const token = localStorage.getItem('token');

            if (!token) {
                redirectToLogin('Token não encontrado');
                return;
            }

            // Verificar se temos um CSRF token válido
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('CSRF token not found - reloading page');
                window.location.reload();
                return;
            }

            fetch('/admin-login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ token: token })
            })
            .then(async response => {
                // Tratar erro 419 (Page Expired / CSRF Token Mismatch)
                if (response.status === 419) {
                    console.error('CSRF token expired - reloading page');
                    window.location.reload();
                    return null;
                }

                return response.json();
            })
            .then(data => {
                if (!data) return; // Se foi 419, já recarregou a página

                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    handleError(data.error, data);
                }
            })
            .catch(error => {
                console.error('Bridge error:', error);
                handleError('Erro de conexão');
            });
        }

        function handleError(errorMessage, responseData = null) {
            console.log('Bridge error:', errorMessage, responseData);

            // Se é erro de token e ainda temos retries
            if (retryCount < MAX_RETRIES && (
                errorMessage.includes('Token') ||
                errorMessage.includes('JWT') ||
                errorMessage.includes('autenticação')
            )) {
                retryCount++;
                console.log(`Tentativa ${retryCount} de ${MAX_RETRIES}`);

                // Tentar novamente após delay
                setTimeout(() => {
                    attemptAdminLogin();
                }, 1000);

                return;
            }

            // Se esgotaram as tentativas ou é erro de permissão
            if (errorMessage.includes('Acesso negado') || errorMessage.includes('permissões insuficientes')) {
                // Usuário não tem permissão para admin, redirecionar para view apropriada
                checkUserRoleAndRedirect();
            } else {
                // Outros erros - limpar e ir para login
                localStorage.removeItem('token');
                localStorage.removeItem('jwt_token');
                redirectToLogin(errorMessage);
            }
        }

        async function checkUserRoleAndRedirect() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/me', {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const user = data.user;

                    // Redirecionar baseado no role
                    if (user.role === 'tecnico') {
                        window.location.href = '/mobile';
                    } else {
                        window.location.href = '/desktop';
                    }
                } else {
                    redirectToLogin('Falha na verificação do usuário');
                }
            } catch (error) {
                redirectToLogin('Erro na verificação do usuário');
            }
        }

        function redirectToLogin(reason) {
            console.log('Redirecionando para login:', reason);

            // Evitar loop verificando se já viemos do login
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('from') === 'admin-bridge') {
                // Se já tentou, mostrar erro
                document.body.innerHTML = `
                    <div style="text-align: center; padding: 50px;">
                        <h2 style="color: #e74c3c;">Erro de Autenticação</h2>
                        <p>${reason}</p>
                        <button onclick="clearAndLogin()" style="padding: 10px 20px; margin-top: 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            Fazer Login Novamente
                        </button>
                    </div>
                `;
            } else {
                window.location.href = '/login?from=admin-bridge';
            }
        }

        function clearAndLogin() {
            localStorage.clear();
            sessionStorage.clear();
            window.location.href = '/login';
        }

        // Debug functions
        window.debugAdminBridge = async function() {
            const token = localStorage.getItem('token');
            console.log('=== DEBUG ADMIN BRIDGE ===');
            console.log('Token presente:', !!token);
            console.log('Token (primeiros 20 chars):', token ? token.substring(0, 20) + '...' : 'null');

            if (token) {
                try {
                    const debugResponse = await fetch('/api/debug-token', {
                        headers: { 'Authorization': 'Bearer ' + token }
                    });
                    const debugData = await debugResponse.json();
                    console.log('Token debug:', debugData);

                    const meResponse = await fetch('/api/me', {
                        headers: { 'Authorization': 'Bearer ' + token }
                    });
                    const meData = await meResponse.json();
                    console.log('User info:', meData);
                } catch (error) {
                    console.error('Debug error:', error);
                }
            }
            console.log('=== END DEBUG ===');
        };

        // Iniciar processo
        attemptAdminLogin();
    </script>
</body>
</html>