import { getMyApplications, normalizeApiResponse, getAuthHeaders } from './api.js';
import { requireAuth } from './auth.js';
import { renderSkeleton, showToast, statusBadge, renderEmptyState } from './ui.js';
import { stopFaviconPulse } from './layout.js';

requireAuth({ role: 'candidato' });
console.log("[TOKEN USADO NA DASHBOARD]", localStorage.getItem("token"));

const list = document.getElementById('applicationsList');
const feedback = document.getElementById('feedback');
const pagination = document.getElementById('applicationsPagination');
let page = 1;
const limit = 8;

function setFeedback(message, type = '') {
  feedback.textContent = message;
  feedback.className = `message ${type}`.trim();
}

function renderPagination(hasItems) {
  if (!pagination) return;
  pagination.innerHTML = `
    <button class="btn btn-ghost" data-page="${Math.max(1, page - 1)}" ${page === 1 ? 'disabled' : ''}>Anterior</button>
    <span>Página ${page}</span>
    <button class="btn btn-ghost" data-page="${page + 1}" ${!hasItems ? 'disabled' : ''}>Próxima</button>
  `;
}

function renderApplications(applications) {
  list.innerHTML = '';

  if (!applications.length) {
    renderEmptyState(list, {
      icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>',
      title: 'Sem Candidaturas',
      message: 'Você ainda não possui candidaturas. Que tal começar agora?',
      ctaText: 'Explorar Vagas',
      ctaHref: './index.html'
    });
    renderPagination(false);
    return;
  }

  list.innerHTML = applications
    .map((item) => `
      <article class="card job-card" style="padding: var(--space-3); display: flex; flex-direction: column;">
        <h3 style="font-size: var(--font-md); font-weight: 700; color: var(--slate-900); margin-bottom: 2px;">
          ${item.titulo}
        </h3>
        
        <div class="company-city mb-1" style="font-size: var(--font-xs); font-weight: 500; color: var(--slate-600);">
          ${item.nome_fantasia} • ${item.cidade || 'Porto Ferreira'}
        </div>

        <div class="meta mb-2" style="font-size: var(--font-xs); color: var(--slate-500);">
          <span>Candidatado em: ${new Date(item.created_at).toLocaleDateString('pt-BR')}</span>
        </div>

        <div class="card-footer mt-auto pt-2" style="display: flex; justify-content: flex-end; border-top: 1px solid var(--slate-50);">
          ${statusBadge(item.status)}
        </div>
      </article>
    `)
    .join('');

  renderPagination(true);
}

async function loadApplications() {
  const token = localStorage.getItem("token");
  if (!token) {
    console.warn("[AUTH] Token não encontrado — redirecionando para login");
    window.location.href = "/ContrataPorto/frontend/pages/login.html";
    return;
  }

  console.log("[DASHBOARD FETCH] Headers:", getAuthHeaders());

  try {
    renderSkeleton(list, 'card', 4);
    const response = await getMyApplications(page, limit);
    const data = normalizeApiResponse(response);
    renderApplications(data);
    stopFaviconPulse();
  } catch (error) {
    list.innerHTML = '';
    showToast(error.message, 'error');
  }
}


pagination?.addEventListener('click', async (event) => {
  const button = event.target.closest('button[data-page]');
  if (!button) return;
  page = Number(button.dataset.page);
  await loadApplications();
});

loadApplications();
