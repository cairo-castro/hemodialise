import './bootstrap';

// Login App Logic
window.loginApp = function() {
    return {
        loading: false,
        error: '',

        loginForm: {
            email: '',
            password: '',
            remember: false
        },

        init() {
            // Verificar se já está logado
            this.checkAuth();
        },

        async checkAuth() {
            // Verificar se é um logout (parâmetro na URL)
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('logout') === 'true') {
                // Se é logout, não verificar token
                localStorage.removeItem('token');
                document.cookie = 'jwt_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
                return;
            }

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
                        this.redirectUser(data.user);
                    } else {
                        localStorage.removeItem('token');
                    }
                } catch (error) {
                    localStorage.removeItem('token');
                }
            }
        },

        async login() {
            this.loading = true;
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
                    // Definir cookie para facilitar acesso pelo backend
                    document.cookie = `jwt_token=${data.token}; path=/; max-age=86400`;
                    this.redirectUser(data.user);
                } else {
                    this.error = data.error || 'Erro no login';
                }
            } catch (error) {
                this.error = 'Erro de conexão';
                console.error('Login failed:', error);
            }

            this.loading = false;
        },

        redirectUser(user) {
            // Redirecionar baseado no role
            if (user.role === 'field_user') {
                window.location.href = '/mobile';
            } else {
                // Para admin/manager, passar pelo bridge para fazer JWT->Session
                window.location.href = '/admin-bridge';
            }
        }
    }
};

// Mobile App Logic
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

        patientForm: {
            full_name: '',
            birth_date: '',
            medical_record: '',
            blood_type: '',
            allergies: '',
            observations: ''
        },

        searchResult: null,
        showPatientForm: false,
        patientSearched: false,

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
                // Limpar localStorage
                localStorage.removeItem('token');

                // Limpar cookie
                document.cookie = 'jwt_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';

                // Logout via sessão web (limpa sessão Laravel)
                await fetch('/logout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Content-Type': 'application/json'
                    }
                });
            } catch (error) {
                console.error('Logout failed:', error);
            }

            // Forçar redirecionamento com parâmetro de logout
            window.location.href = '/login?logout=true';
        },

        async searchPatient() {
            if (!this.patientForm.full_name || !this.patientForm.birth_date) {
                alert('Por favor, preencha o nome completo e a data de nascimento.');
                return;
            }

            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/patients/search', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    },
                    body: JSON.stringify({
                        full_name: this.patientForm.full_name,
                        birth_date: this.patientForm.birth_date
                    })
                });

                const data = await response.json();
                this.patientSearched = true;

                if (data.found) {
                    this.searchResult = data.patient;
                    this.showPatientForm = false;
                    // Preencher o formulário com os dados encontrados
                    this.patientForm = {
                        full_name: data.patient.full_name,
                        birth_date: data.patient.birth_date,
                        medical_record: data.patient.medical_record,
                        blood_type: data.patient.blood_type || '',
                        allergies: '',
                        observations: ''
                    };
                    // Usar o paciente encontrado no checklist
                    this.checklistForm.patient_id = data.patient.id;
                } else {
                    this.searchResult = null;
                    this.showPatientForm = true;
                }
            } catch (error) {
                console.error('Patient search failed:', error);
                alert('Erro de conexão ao buscar paciente');
            }
        },

        async savePatient() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/patients', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                    },
                    body: JSON.stringify(this.patientForm)
                });

                const data = await response.json();

                if (data.success) {
                    this.searchResult = data.patient;
                    this.showPatientForm = false;
                    this.checklistForm.patient_id = data.patient.id;
                    alert('Paciente cadastrado com sucesso!');
                } else {
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join('\n');
                        alert('Erro de validação:\n' + errorMessages);
                    } else {
                        alert('Erro: ' + (data.message || 'Falha ao cadastrar paciente'));
                    }
                }
            } catch (error) {
                console.error('Patient save failed:', error);
                alert('Erro de conexão ao cadastrar paciente');
            }
        },

        startNewPatientSearch() {
            this.patientForm = {
                full_name: '',
                birth_date: '',
                medical_record: '',
                blood_type: '',
                allergies: '',
                observations: ''
            };
            this.searchResult = null;
            this.showPatientForm = false;
            this.patientSearched = false;
            this.checklistForm.patient_id = '';
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
