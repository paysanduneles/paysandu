// Authentication Module
const Auth = {
    usuarioAtual: null,
    adminAtual: null,

    // Initialize
    init: () => {
        // Carregar usuário do localStorage (para manter sessão)
        Auth.usuarioAtual = Utils.storage.get('usuarioAtual');
        Auth.adminAtual = Utils.storage.get('adminAtual');
    },

    // Login user
    login: async (telefone, senha) => {
        try {
            const response = await API.auth.login(telefone, senha);
            
            if (response.sucesso) {
                Auth.usuarioAtual = response.usuario;
                Utils.storage.set('usuarioAtual', response.usuario);
                return { sucesso: true, usuario: response.usuario };
            }
            
            return { sucesso: false, mensagem: response.mensagem };
        } catch (error) {
            return { sucesso: false, mensagem: error.message };
        }
    },

    // Register user
    register: async (dadosUsuario) => {
        try {
            const response = await API.auth.register(dadosUsuario);
            
            if (response.sucesso) {
                Auth.usuarioAtual = response.usuario;
                Utils.storage.set('usuarioAtual', response.usuario);
                return { sucesso: true, usuario: response.usuario };
            }
            
            return { sucesso: false, erros: response.erros };
        } catch (error) {
            return { sucesso: false, mensagem: error.message };
        }
    },

    // Forgot password
    forgotPassword: async (telefone) => {
        try {
            const response = await API.auth.forgotPassword(telefone);
            return response;
        } catch (error) {
            return { sucesso: false, mensagem: error.message };
        }
    },

    // Admin login
    adminLogin: async (nomeUsuario, senha) => {
        try {
            const response = await API.auth.adminLogin(nomeUsuario, senha);
            
            if (response.sucesso) {
                Auth.adminAtual = response.admin;
                Utils.storage.set('adminAtual', response.admin);
                return { sucesso: true, admin: response.admin };
            }
            
            return { sucesso: false, mensagem: response.mensagem };
        } catch (error) {
            return { sucesso: false, mensagem: error.message };
        }
    },

    // Logout
    logout: () => {
        Auth.usuarioAtual = null;
        Auth.adminAtual = null;
        Utils.storage.remove('usuarioAtual');
        Utils.storage.remove('adminAtual');
    },

    // Check if user is logged in
    isLoggedIn: () => {
        return Auth.usuarioAtual !== null;
    },

    // Check if admin is logged in
    isAdminLoggedIn: () => {
        return Auth.adminAtual !== null;
    },

    // Get current user
    getCurrentUser: () => {
        return Auth.usuarioAtual;
    },

    // Get current admin
    getCurrentAdmin: () => {
        return Auth.adminAtual;
    }
};

// Form handlers
document.addEventListener('DOMContentLoaded', () => {
    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(loginForm);
            const telefone = formData.get('phone');
            const senha = formData.get('password');

            Utils.setLoading(true);
            const result = await Auth.login(telefone, senha);
            Utils.setLoading(false);
            
            if (result.sucesso) {
                Utils.showMessage('Login realizado com sucesso!');
                setTimeout(() => {
                    App.showMainApp();
                }, 1000);
            } else {
                Utils.showMessage(result.mensagem, 'error');
            }
        });
    }

    // Register form
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(registerForm);
            const dadosUsuario = {
                name: formData.get('name'),
                phone: formData.get('phone'),
                email: formData.get('email'),
                address: formData.get('address'),
                number: formData.get('number'),
                complement: formData.get('complement'),
                city: formData.get('city'),
                password: formData.get('password'),
                confirmPassword: formData.get('confirmPassword')
            };

            Utils.setLoading(true);
            const result = await Auth.register(dadosUsuario);
            Utils.setLoading(false);
            
            if (result.sucesso) {
                Utils.showMessage('Conta criada com sucesso!');
                setTimeout(() => {
                    App.showMainApp();
                }, 1000);
            } else {
                if (result.erros) {
                    // Show field-specific errors
                    for (const field in result.erros) {
                        const fieldEl = document.querySelector(`[name="${field}"]`);
                        if (fieldEl) {
                            const formGroup = fieldEl.closest('.form-group');
                            formGroup.classList.add('error');
                            
                            let errorEl = formGroup.querySelector('.error-message');
                            if (!errorEl) {
                                errorEl = document.createElement('small');
                                errorEl.className = 'error-message';
                                formGroup.appendChild(errorEl);
                            }
                            errorEl.textContent = result.erros[field];
                        }
                    }
                } else {
                    Utils.showMessage(result.mensagem, 'error');
                }
            }
        });

        // Clear errors on input
        registerForm.addEventListener('input', (e) => {
            const formGroup = e.target.closest('.form-group');
            if (formGroup.classList.contains('error')) {
                formGroup.classList.remove('error');
                const errorEl = formGroup.querySelector('.error-message');
                if (errorEl) {
                    errorEl.remove();
                }
            }
        });
    }

    // Forgot password form
    const forgotForm = document.getElementById('forgot-password-form');
    if (forgotForm) {
        forgotForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(forgotForm);
            const telefone = formData.get('phone');

            Utils.setLoading(true);
            const result = await Auth.forgotPassword(telefone);
            Utils.setLoading(false);
            
            if (result.sucesso) {
                Utils.showMessage(result.mensagem);
            } else {
                Utils.showMessage(result.mensagem, 'error');
            }
        });
    }

    // Admin login form
    const adminLoginForm = document.getElementById('admin-login-form');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(adminLoginForm);
            const nomeUsuario = formData.get('username');
            const senha = formData.get('password');

            Utils.setLoading(true);
            const result = await Auth.adminLogin(nomeUsuario, senha);
            Utils.setLoading(false);
            
            if (result.sucesso) {
                Utils.showMessage('Login realizado com sucesso!');
                document.getElementById('admin-login').style.display = 'none';
                document.getElementById('admin-panel').style.display = 'flex';
                Admin.init();
            } else {
                Utils.showMessage(result.mensagem, 'error');
            }
        });
    }
});

// Navigation functions
function showLogin() {
    document.getElementById('login-page').style.display = 'flex';
    document.getElementById('register-page').style.display = 'none';
    document.getElementById('forgot-password-page').style.display = 'none';
}

function showRegister() {
    document.getElementById('login-page').style.display = 'none';
    document.getElementById('register-page').style.display = 'flex';
    document.getElementById('forgot-password-page').style.display = 'none';
}

function showForgotPassword() {
    document.getElementById('login-page').style.display = 'none';
    document.getElementById('register-page').style.display = 'none';
    document.getElementById('forgot-password-page').style.display = 'flex';
}

function logout() {
    Auth.logout();
    Cart.clearCart(); // Limpar carrinho ao fazer logout
    Utils.showMessage('Logout realizado com sucesso!');
    setTimeout(() => {
        App.showMainApp(); // Volta para o cardápio mas agora sem estar logado
    }, 1000);
}

function adminLogout() {
    Auth.logout();
    Utils.showMessage('Logout realizado com sucesso!');
    setTimeout(() => {
        window.location.href = '/';
    }, 1000);
}

// Initialize auth
Auth.init();