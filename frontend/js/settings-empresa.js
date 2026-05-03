import { API_URL } from './config.js';

// ── auth ──────────────────────────────────────────────
const token = localStorage.getItem('token');
const role  = localStorage.getItem('user_role');

if (!token || role?.toLowerCase() !== 'empresa') {
  window.location.href = './login.html';
}

// ── elementos ─────────────────────────────────────────
const avatarEl       = document.getElementById('avatar-initials');
const displayNome    = document.getElementById('display-nome');
const displayEmail   = document.getElementById('display-email');
const inputNome      = document.getElementById('input-nome');
const inputEmail     = document.getElementById('input-email');
const inputTelefone  = document.getElementById('input-telefone');
const btnSalvar      = document.getElementById('btn-salvar-perfil');
const feedbackPerfil = document.getElementById('feedback-perfil');
const inputConfirmar = document.getElementById('input-confirmar');
const btnExcluir     = document.getElementById('btn-excluir');
const feedbackExclui = document.getElementById('feedback-exclusao');

// ── utilitários ───────────────────────────────────────
function getInitials(nome) {
  if (!nome) return '?';
  const parts = nome.trim().split(' ').filter(Boolean);
  if (parts.length === 1) return parts[0][0].toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

function showFeedback(el, msg, tipo) {
  el.textContent  = msg;
  el.className    = 'feedback ' + tipo;
  setTimeout(() => { el.className = 'feedback'; }, 4000);
}

function setLoading(btn, loading) {
  btn.disabled    = loading;
  btn.textContent = loading ? 'Salvando...' : 'Salvar alterações';
}

// ── carregar dados ────────────────────────────────────
async function loadProfile() {
  try {
    const res  = await fetch(API_URL + '/empresa/profile', {
      headers: { 'Authorization': 'Bearer ' + token }
    });
    const json = await res.json();
    if (!res.ok) throw new Error(json.message);

    const d = json.data;
    inputNome.value     = d.nome_fantasia || '';
    inputEmail.value    = d.email_contato || '';
    inputTelefone.value = d.telefone      || '';

    displayNome.textContent  = d.nome_fantasia || 'Sem nome';
    displayEmail.textContent = d.email_contato || '';
    avatarEl.textContent     = getInitials(d.nome_fantasia);
  } catch (err) {
    showFeedback(feedbackPerfil, 'Erro ao carregar dados da empresa.', 'error');
  }
}

// ── salvar perfil ─────────────────────────────────────
btnSalvar.addEventListener('click', async () => {
  const nome_fantasia = inputNome.value.trim();
  const telefone      = inputTelefone.value.trim();

  if (!nome_fantasia) {
    showFeedback(feedbackPerfil, 'O nome da empresa não pode ficar vazio.', 'error');
    return;
  }

  setLoading(btnSalvar, true);

  try {
    const res  = await fetch(API_URL + '/empresa/profile', {
      method:  'PUT',
      headers: {
        'Content-Type':  'application/json',
        'Authorization': 'Bearer ' + token
      },
      body: JSON.stringify({ nome_fantasia, telefone })
    });
    const json = await res.json();

    if (!res.ok) throw new Error(json.message);

    // Atualiza exibição
    displayNome.textContent  = nome_fantasia;
    avatarEl.textContent     = getInitials(nome_fantasia);

    // Atualiza header se existir
    const headerNome = document.getElementById('headerUserName');
    if (headerNome) headerNome.textContent = nome_fantasia;

    showFeedback(feedbackPerfil, '✅ Dados atualizados com sucesso!', 'success');
  } catch (err) {
    showFeedback(feedbackPerfil, err.message || 'Erro ao salvar.', 'error');
  } finally {
    setLoading(btnSalvar, false);
    btnSalvar.textContent = 'Salvar alterações';
  }
});

// ── zona de perigo ────────────────────────────────────
inputConfirmar.addEventListener('input', () => {
  btnExcluir.disabled = inputConfirmar.value !== 'EXCLUIR';
});

btnExcluir.addEventListener('click', async () => {
  if (!confirm('Tem certeza? Esta ação não pode ser desfeita.')) return;

  btnExcluir.disabled     = true;
  btnExcluir.textContent  = 'Excluindo...';

  try {
    const res = await fetch(API_URL + '/empresa/profile', {
      method:  'DELETE',
      headers: { 'Authorization': 'Bearer ' + token }
    });
    if (!res.ok) throw new Error('Erro ao excluir conta.');

    localStorage.clear();
    window.location.href = '../pages/index.html';
  } catch (err) {
    showFeedback(feedbackExclui, err.message, 'error');
    btnExcluir.disabled    = false;
    btnExcluir.textContent = 'Excluir minha conta';
  }
});

// ── inicializa ────────────────────────────────────────
loadProfile();
