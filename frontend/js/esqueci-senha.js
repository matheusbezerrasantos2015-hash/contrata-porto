import { API_URL } from './config.js';

const btnEnviar   = document.getElementById('btn-enviar');
const emailInput  = document.getElementById('email');
const formStep    = document.getElementById('form-step');
const successStep = document.getElementById('success-step');
const loadingStep = document.getElementById('loading-step');
const errorMsg    = document.getElementById('error-msg');

btnEnviar.addEventListener('click', async () => {
  const email = emailInput.value.trim();
  if (!email) {
    mostrarErro('Por favor, digite seu e-mail.');
    return;
  }

  formStep.style.display    = 'none';
  loadingStep.style.display = 'block';
  errorMsg.style.display    = 'none';

  try {
    const res = await fetch(API_URL + '/auth/recover', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email })
    });
    
    // Sempre mostra sucesso (não revela se email existe) para segurança
    loadingStep.style.display = 'none';
    document.getElementById('email-enviado').textContent = email;
    successStep.style.display = 'block';
  } catch (err) {
    console.error('[RECOVER_ERROR]', err);
    loadingStep.style.display = 'none';
    formStep.style.display    = 'block';
    mostrarErro('Erro de conexão. Tente novamente.');
  }
});

function mostrarErro(msg) {
  errorMsg.textContent    = msg;
  errorMsg.style.display  = 'block';
}
