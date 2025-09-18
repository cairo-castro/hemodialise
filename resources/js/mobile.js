window.mobileApp = function() {
    return {
        loading: true,
        user: null,
        currentView: 'dashboard',
        online: navigator.onLine,
        error: '',
        loggingIn: false,

        loginForm: {
            email: '',
            password: ''
        },

        checklistForm: {
            machine_id: '',
            patient_id: '',
            shift: '',
            checklist_data: {},
            observations: ''
        },

        init() {
            this.checkAuth();
            this.setupOfflineHandlers();
            this.registerServiceWorker();

            setTimeout(() => {
                this.loading = false;
            }, 1500);
        },

        async checkAuth() {
            const token = localStorage.getItem('token');
            if (token) {
                try {
                    const response = await fetch('/api/me', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.user = data.user;
                    } else {
                        localStorage.removeItem('token');
                    }
                } catch (error) {
                    console.error('Auth check failed:', error);
                    localStorage.removeItem('token');
                }
            }
        },

        async login() {
            this.loggingIn = true;
            this.error = '';

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.loginForm)
                });

                const data = await response.json();

                if (response.ok) {
                    localStorage.setItem('token', data.token);
                    this.user = data.user;
                    this.loginForm.email = '';
                    this.loginForm.password = '';
                } else {
                    this.error = data.error || 'Erro no login';
                }
            } catch (error) {
                this.error = 'Erro de conexão';
                console.error('Login failed:', error);
            }

            this.loggingIn = false;
        },

        async logout() {
            try {
                const token = localStorage.getItem('token');
                if (token) {
                    await fetch('/api/logout', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                        }
                    });
                }
            } catch (error) {
                console.error('Logout failed:', error);
            }

            localStorage.removeItem('token');
            this.user = null;
            this.currentView = 'dashboard';
        },

        async submitChecklist() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/checklists', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    },
                    body: JSON.stringify(this.checklistForm)
                });

                if (response.ok) {
                    // Reset form and show success
                    this.checklistForm = {
                        machine_id: '',
                        patient_id: '',
                        shift: '',
                        checklist_data: {},
                        observations: ''
                    };
                    alert('Checklist salvo com sucesso!');
                } else {
                    const data = await response.json();
                    alert('Erro: ' + (data.message || 'Falha ao salvar'));
                }
            } catch (error) {
                console.error('Submit failed:', error);
                alert('Erro de conexão');
            }
        },

        setupOfflineHandlers() {
            window.addEventListener('online', () => {
                this.online = true;
                console.log('Back online');
            });

            window.addEventListener('offline', () => {
                this.online = false;
                console.log('Gone offline');
            });
        },

        async registerServiceWorker() {
            if ('serviceWorker' in navigator) {
                try {
                    await navigator.serviceWorker.register('/sw.js');
                    console.log('Service Worker registered');
                } catch (error) {
                    console.error('Service Worker registration failed:', error);
                }
            }
        }
    }
}