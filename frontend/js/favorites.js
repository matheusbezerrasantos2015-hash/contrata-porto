import { getFavorites, removeFavorite, normalizeApiResponse } from './api.js';
import { requireAuth } from './auth.js';
import { confirmAction, renderSkeleton, setButtonLoading, showToast, renderEmptyState } from './ui.js';
import { escapeHTML } from './utils.js';

requireAuth({ role: 'candidato' });

const list = document.getElementById('favoritesList');

function renderFavorites(items) {
  list.innerHTML = '';

  if (!items.length) {
    renderEmptyState(list, {
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>',
      title: 'Vagas Salvas Vazia',
      message: 'Você ainda não salvou nenhuma vaga para ver depois.',
      ctaText: 'Explorar Vagas',
      ctaHref: './index.html'
    });
    return;
  }

  list.innerHTML = items
    .map((item) => `
      <article class="card job-card">
        <div class="company">${escapeHTML(item.empresa_nome || 'Empresa Local')}</div>
        <h3>${escapeHTML(item.titulo)}</h3>
        <div class="meta">
          <span>${escapeHTML(item.cidade || 'Porto Ferreira')}</span>
          <span>•</span>
          <span>${escapeHTML(item.tipo_contrato || 'CLT')}</span>
        </div>
        <div class="card-actions" style="margin-top: auto; padding-top: 16px;">
          <a class="btn btn-secondary" href="./job.html?id=${encodeURIComponent(item.vaga_id)}" style="flex: 1;">Ver detalhes</a>
          <button class="btn btn-ghost" data-remove-id="${item.id}" title="Remover dos favoritos" style="color: var(--error-600);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="var(--error-500)" stroke="currentColor" stroke-width="2">
              <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
            </svg>
          </button>
        </div>
      </article>
    `)
    .join('');
}

async function loadFavorites() {
  const token = localStorage.getItem("token");
  if (!token) {
    console.warn("[AUTH] Token não encontrado — redirecionando para login");
    window.location.href = "/ContrataPorto/frontend/pages/login.html";
    return;
  }
  try {
    renderSkeleton(list, 3);
    const response = await getFavorites();
    const data = normalizeApiResponse(response);
    renderFavorites(data);
  } catch (error) {
    list.innerHTML = '';
    showToast(error.message, 'error');
  }
}


list?.addEventListener('click', async (event) => {
  const button = event.target.closest('button[data-remove-id]');
  if (!button) return;

  if (!confirmAction('Deseja remover esta vaga dos favoritos?')) {
    return;
  }

  try {
    setButtonLoading(button, true, 'Removendo...');
    await removeFavorite(Number(button.dataset.removeId));
    showToast('Vaga removida dos favoritos.', 'success');
    await loadFavorites();
  } catch (error) {
    showToast(error.message, 'error');
    setButtonLoading(button, false);
  }
});

loadFavorites();
