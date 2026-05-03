import { API_URL } from './config.js';

// Pega o token da URL
const params = new URLSearchParams(window.location.search);
const token  = params.get('token');

const resetFormContainer = document.getElementById('reset-form-container');
const successStep        = document.getElementById('success-step');
const invalidTokenStep   = document.getElementById('invalid-token-step');
const errorMsg           = document.getElementById('error-msg');
const btnSalvar          = document.getElementById('btn-salvar');

// Se não tem token: mostra erro de link inválido imediatamente
if (!token) {
  mostrarErroLink();
}

btnSalvar.addEventListener('click', async () => {
  const nova = document.getElementById('nova-senha').value;
  const conf = document.getElementById('conf-senha').value;

  if (nova.length < 6) {
    mostrarErro('A senha deve ter pelo menos 6 caracteres.');
    return;
  }
  if (nova !== conf) {
    mostrarErro('As senhas não coincidem.');
    return;
  }

  errorMsg.style.display = 'none';
  btnSalvar.disabled = true;
  btnSalvar.textContent = 'Salvando...';

  try {
    const res = await fetch(API_URL + '/auth/reset', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ token, nova_senha: nova })
    });
    
    const data = await res.json();

    if (res.ok) {
      mostrarSucesso();
    } else {
      console.error('[RESET_ERROR]', data);
      mostrarErroLink(); // token inválido/expirado
    }
  } catch (err) {
    console.error('[RESET_CONNECTION_ERROR]', err);
    mostrarErro('Erro de conexão. Tente novamente.');
    btnSalvar.disabled = false;
    btnSalvar.textContent = 'Salvar nova senha';
  }
});

function mostrarSucesso() {
  resetFormContainer.style.display = 'none';
  invalidTokenStep.style.display   = 'none';
  successStep.style.display        = 'block';
  errorMsg.style.display           = 'none';
}

function mostrarErroLink() {
  resetFormContainer.style.display = 'none';
  successStep.style.display        = 'none';
  invalidTokenStep.style.display   = 'block';
  errorMsg.style.display           = 'none';
}

function mostrarErro(msg) {
  errorMsg.textContent   = msg;
  errorMsg.style.display = 'block';
}
