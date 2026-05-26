import { isAuthenticated, redirectIfAuthenticated } from './auth.js';
import { login } from './api.js';
import { setButtonLoading, showToast, withGlobalLoader } from './ui.js';

// Redireciona se já estiver logado
redirectIfAuthenticated();

const form = document.getElementById('loginForm');
const message = document.getElementById('message');
const submitBtn = form.querySelector('button[type="submit"]');

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  message.textContent = '';
  message.className = 'message';

  const email = form.email.value.trim();
  const senha = form.senha.value;

  localStorage.removeItem('token');
  setButtonLoading(submitBtn, true, 'Entrando...');
  try {
    const result = await withGlobalLoader(() => login({ email, senha }));

    // Caminho confirmado: result = { success, message, data: { auth: { token }, usuario: { id, role } } }
    const token = result?.data?.auth?.token || null;
    
    if (!token || token.length < 20) {
      alert("Erro de autenticação: token não recebido corretamente");
      setButtonLoading(submitBtn, false);
      return;
    }
    
    localStorage.setItem("token", token);
    
    const user = result?.data?.usuario || {};
    localStorage.setItem('user_id', String(user.id || ''));
    localStorage.setItem('user_role', String(user.role || 'candidato').toLowerCase());

    message.textContent = result?.message || 'Login realizado com sucesso.';
    message.classList.add('success');
    showToast('Login realizado com sucesso.', 'success');

    const params = new URLSearchParams(window.location.search);
    const redirect = params.get('redirect');
    if (redirect) {
      window.location.href = redirect;
      return;
    }

    window.location.href = user.role === 'empresa' ? './dashboard.html' : './candidate-dashboard.html';
  } catch (error) {

    // Trata caso de e-mail não verificado (HTTP 403)
    if (error.status === 403 && error.payload && error.payload.data && error.payload.data.requires_verification) {
      sessionStorage.setItem('pending_verification_email', error.payload.data.email);
      showToast(error.message, 'warning');
      setTimeout(() => {
        window.location.href = './verificar-email.html?msg=unverified';
      }, 1500);
      return;
    }

    message.textContent = error.message;
    message.classList.add('error');
    showToast(error.message, 'error');
    setButtonLoading(submitBtn, false);
  }
});
