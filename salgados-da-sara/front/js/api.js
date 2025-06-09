// API Service Module
const API = {
    baseURL: 'http://localhost/salgados-da-sara/backend/api', // URL corrigida para XAMPP
    
    // Helper para fazer requisições
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            console.log('Fazendo requisição para:', url); // Debug
            const response = await fetch(url, config);
            
            // Log da resposta para debug
            console.log('Status da resposta:', response.status);
            console.log('Headers da resposta:', response.headers);
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Tentar ler como texto para ver o que está sendo retornado
                const text = await response.text();
                console.error('Resposta não é JSON:', text);
                throw new Error(`Backend retornou: ${text.substring(0, 200)}...`);
            }
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.mensagem || 'Erro na requisição');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            
            // Verificar se é erro de rede
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                throw new Error('Erro de conexão. Verifique se o XAMPP está rodando e se o backend está acessível.');
            }
            
            throw error;
        }
    },

    // Métodos de autenticação
    auth: {
        async login(telefone, senha) {
            return API.request('/auth/login', {
                method: 'POST',
                body: JSON.stringify({ phone: telefone, password: senha })
            });
        },

        async register(dadosUsuario) {
            return API.request('/auth/register', {
                method: 'POST',
                body: JSON.stringify(dadosUsuario)
            });
        },

        async forgotPassword(telefone) {
            return API.request('/auth/forgot-password', {
                method: 'POST',
                body: JSON.stringify({ phone: telefone })
            });
        },

        async adminLogin(nomeUsuario, senha) {
            return API.request('/auth/admin-login', {
                method: 'POST',
                body: JSON.stringify({ username: nomeUsuario, password: senha })
            });
        }
    },

    // Métodos de produtos
    products: {
        async getAll() {
            return API.request('/products');
        },

        async create(dadosProduto) {
            return API.request('/products/create', {
                method: 'POST',
                body: JSON.stringify(dadosProduto)
            });
        },

        async update(dadosProduto) {
            return API.request('/products/update', {
                method: 'POST',
                body: JSON.stringify(dadosProduto)
            });
        },

        async delete(id) {
            return API.request('/products/delete', {
                method: 'POST',
                body: JSON.stringify({ id })
            });
        }
    },

    // Métodos de pedidos
    orders: {
        async getAll(usuarioId = null) {
            const endpoint = usuarioId ? `/orders?user_id=${usuarioId}` : '/orders';
            return API.request(endpoint);
        },

        async create(dadosPedido) {
            return API.request('/orders/create', {
                method: 'POST',
                body: JSON.stringify(dadosPedido)
            });
        },

        async updateStatus(id, status, descricao = null, motivoRejeicao = null) {
            return API.request('/orders/update-status', {
                method: 'POST',
                body: JSON.stringify({ id, status, description: descricao, rejection_reason: motivoRejeicao })
            });
        }
    },

    // Métodos de administração
    admin: {
        async getAdmins() {
            return API.request('/admin/admins');
        },

        async createAdmin(dadosAdmin) {
            return API.request('/admin/admins', {
                method: 'POST',
                body: JSON.stringify(dadosAdmin)
            });
        },

        async deleteAdmin(id) {
            return API.request('/admin/admins', {
                method: 'DELETE',
                body: JSON.stringify({ id })
            });
        }
    },

    // Métodos de configuração
    config: {
        async getAll() {
            return API.request('/config');
        },

        async get(chave) {
            return API.request(`/config?key=${chave}`);
        },

        async set(chave, valor) {
            return API.request('/config', {
                method: 'POST',
                body: JSON.stringify({ key: chave, value: valor })
            });
        }
    }
};

// Tornar API globalmente disponível
window.API = API;