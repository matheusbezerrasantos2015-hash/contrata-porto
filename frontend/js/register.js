import { register } from './api.js';
import { redirectIfAuthenticated } from './auth.js';
import { setButtonLoading, showToast, withGlobalLoader } from './ui.js';

redirectIfAuthenticated();

const form = document.getElementById('registerForm');
const message = document.getElementById('message');
const submitBtn = form.querySelector('button[type="submit"]');

const companyConfig = document.getElementById('companyConfig');
const companyInputs = document.querySelectorAll('.company-input');
const radios = document.querySelectorAll('input[name="perfil"]');
const nomeLabel = document.getElementById('nomeLabel');

radios.forEach(radio => {
  radio.addEventListener('change', (e) => {
    if (e.target.value === 'EMPRESA') {
      companyConfig.style.display = 'block';
      if (nomeLabel) nomeLabel.textContent = 'Seu nome (Responsável)';
      // Apenas o email_contato é opcional
      companyInputs.forEach(input => input.required = input.name !== 'email_contato');
    } else {
      companyConfig.style.display = 'none';
      if (nomeLabel) nomeLabel.textContent = 'Nome completo';
      companyInputs.forEach(input => input.required = false);
    }
  });
});

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  message.textContent = '';
  message.className = 'message';

  const role = form.perfil.value;
  const payload = {
    nome: form.nome.value.trim(),
    email: form.email.value.trim(),
    senha: form.senha.value,
    role: role
  };

  if (role === 'EMPRESA') {
    payload.company = {
      nome_fantasia: form.nome_fantasia.value.trim(),
      razao_social: form.razao_social.value.trim(),
      cnpj: form.cnpj.value.trim().replace(/\D/g, ''),
      email_contato: form.email_contato.value.trim() || form.email.value.trim()
    };
  }

  setButtonLoading(submitBtn, true, 'Cadastrando...');
  try {
    const result = await withGlobalLoader(() => register(payload));
    message.textContent = result.message || 'Cadastro realizado com sucesso.';
    message.classList.add('success');
    showToast(result.message || 'Verifique seu e-mail para confirmar a conta.', 'success');
    
    // Salva o e-mail para usar na tela de verificação
    sessionStorage.setItem('pending_verification_email', form.email.value.trim());
    
    form.reset();

    setTimeout(() => {
      window.location.href = './verificar-email.html';
    }, 1500);
  } catch (error) {
    message.textContent = error.message;
    message.classList.add('error');
    showToast(error.message, 'error');
    setButtonLoading(submitBtn, false);
  }
});
