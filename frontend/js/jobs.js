import { applyToJob, getFavorites, getJobs, getMyApplications, removeFavorite, saveFavorite, normalizeApiResponse } from './api.js';
import { isAuthenticated, redirectToLogin } from './auth.js';
import { renderSkeleton, setButtonLoading, showToast, withGlobalLoader, renderEmptyState } from './ui.js';
import { stopFaviconPulse } from './layout.js';
import { escapeHTML } from './utils.js';

const jobsList = document.getElementById('jobsList');
const filtersForm = document.getElementById('filtersForm');
const sortSelect = document.getElementById('sortSelect');

let currentPage = 1;
const limit = 40;
let lastFilters = {};
let currentItems = [];
const appliedJobs = new Set();
const favoritesMap = new Map();

function setFeedback(message, type = '') {
  const feedback = document.getElementById('feedback');
  if (!feedback) return;
  feedback.textContent = message;
  feedback.className = `message ${type}`.trim();
}

function buildQueryString(filters, page = 1) {
  const query = new URLSearchParams({ page: String(page), limit: String(limit) });
  Object.entries(filters).forEach(([key, value]) => {
    if (value) query.append(key, value);
  });
  return query.toString();
}

function renderPagination(p) {
  const container = document.getElementById('pagination') || document.getElementById('jobsPagination');
  if (!container) return;
  if (p.total_pages <= 1) {
    container.innerHTML = '';
    return;
  }

  let html = '<div class="pagination-wrapper">';

  // Botão anterior
  if (p.has_prev) {
    html += `<button class="btn-page" data-page="${p.page - 1}">
               ← Anterior
             </button>`;
  }

  // Indicador de página
  html += `<span class="page-info">
             Página ${p.page} de ${p.total_pages}
             <small>(${p.total} vagas encontradas)</small>
           </span>`;

  // Botão próxima
  if (p.has_next) {
    html += `<button class="btn-page" data-page="${p.page + 1}">
               Próxima →
             </button>`;
  }

  html += '</div>';
  container.innerHTML = html;

  // Event listeners nos botões
  container.querySelectorAll('.btn-page').forEach(btn => {
    btn.addEventListener('click', () => {
      const newPage = btn.dataset.page;
      const params = new URLSearchParams(window.location.search);
      params.set('page', newPage);
      window.history.replaceState({}, '', '?' + params.toString());
      loadJobs(); // recarrega com nova página
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });
}

function isNewJob(job) {
  if (!job.publicada_em) return false;
  const diff = Date.now() - new Date(job.publicada_em).getTime();
  return diff < 1000 * 60 * 60 * 24 * 7;
}

function sortItems(items) {
  const mode = sortSelect?.value || 'recentes';
  const copy = [...items];

  if (mode === 'relevantes') {
    return copy.sort((a, b) => String(a.titulo).localeCompare(String(b.titulo), 'pt-BR'));
  }

  return copy.sort((a, b) => new Date(b.publicada_em || 0) - new Date(a.publicada_em || 0));
}

function createJobCard(job) {
  const article = document.createElement('article');
  article.className = 'card job-card';

  const hasApplied = appliedJobs.has(job.id);
  const hasSaved = favoritesMap.has(job.id);

  // Estrutura HTML estática — dados da API escapados com escapeHTML para prevenir XSS
  article.innerHTML = `
    <div class="company">${escapeHTML(job.empresa_nome || 'Empresa Local')}</div>
    <h3>${escapeHTML(job.titulo)}</h3>
    <div class="meta">
      <span>${escapeHTML(job.cidade || 'Porto Ferreira')}</span>
      <span>•</span>
      <span>${escapeHTML(job.tipo_contrato || 'CLT')}</span>
    </div>
    ${job.total_candidatos > 0 ? `<span class="job-candidates">👥 ${escapeHTML(String(job.total_candidatos))} candidato(s)</span>` : ''}
    <div class="chips" style="margin-top: 8px;">
      ${isNewJob(job) ? '<span class="badge badge-info">Nova</span>' : ''}
      ${job.nivel ? `<span class="chip">${escapeHTML(job.nivel)}</span>` : ''}
    </div>
    <div class="card-actions" style="margin-top: auto; padding-top: 16px;">
      <a class="btn btn-secondary" href="./job.html?id=${encodeURIComponent(job.id)}" style="flex: 1;">Ver detalhes</a>
      <button class="btn btn-ghost" data-favorite-id="${encodeURIComponent(job.id)}" title="${hasSaved ? 'Remover dos favoritos' : 'Salvar vaga'}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="${hasSaved ? 'var(--brand-500)' : 'none'}" stroke="currentColor" stroke-width="2">
          <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
        </svg>
      </button>
    </div>
    <img src="../assets/favicon.png" alt="" class="job-card-watermark" aria-hidden="true">
  `;
  return article;
}

function renderJobs(items) {
  jobsList.innerHTML = '';

  if (!items || !items.length) {
    renderEmptyState(jobsList, {
      title: 'Nenhuma vaga encontrada',
      message: 'Tente ajustar os filtros ou pesquisar por termos mais genéricos.',
      ctaText: 'Limpar todos os filtros',
      ctaId: 'clearFiltersBtn'
    });
    document.getElementById('clearFiltersBtn')?.addEventListener('click', clearFilters);
    return;
  }

  sortItems(items).forEach((job) => jobsList.appendChild(createJobCard(job)));
}

async function preloadUserStates() {
  if (!isAuthenticated()) return;

  try {
    const [applicationsResponse, favoritesResponse] = await Promise.all([
      getMyApplications(1, 100),
      getFavorites()
    ]);

    const applicationsData = normalizeApiResponse(applicationsResponse);
    const applications = Array.isArray(applicationsData) ? applicationsData : (applicationsData.items || []);
    applications.forEach((application) => {
      appliedJobs.add(Number(application.vaga_id));
    });

    const favoritesData = normalizeApiResponse(favoritesResponse);
    const favorites = Array.isArray(favoritesData) ? favoritesData : (favoritesData.items || []);
    favorites.forEach((favorite) => {
      favoritesMap.set(Number(favorite.vaga_id), Number(favorite.vaga_id));
    });
  } catch (_) {
    // não bloqueia a página se houver erro em estado auxiliar
  }
}

async function handleApply(button, jobId) {
  if (!isAuthenticated()) {
    redirectToLogin(window.location.pathname + window.location.search);
    return;
  }

  // Com o novo fluxo de modal e currículo, redirecionamos para os detalhes
  window.location.href = `./job.html?id=${jobId}&apply=true`;
}

async function handleFavorite(button, jobId) {
  if (!isAuthenticated()) {
    redirectToLogin(window.location.pathname + window.location.search);
    return;
  }

  const jobKey = Number(jobId);
  const favoriteId = favoritesMap.get(jobKey);

  if (!favoriteId) {
    setButtonLoading(button, true, 'Salvando...');
    try {
      await saveFavorite(jobKey);
      favoritesMap.set(jobKey, jobKey);
      button.textContent = 'Salvo';
      showToast('Vaga salva', 'success');
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      setButtonLoading(button, false);
    }
    return;
  }

  setButtonLoading(button, true, 'Removendo...');
  try {
    await removeFavorite(favoriteId);
    favoritesMap.delete(jobKey);
    button.textContent = 'Salvar vaga';
    showToast('Vaga removida dos favoritos', 'info');
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    setButtonLoading(button, false);
  }
}

async function loadJobs() {
  try {
    setFeedback('');
    renderSkeleton(jobsList, 'card', 6);

    const params = new URLSearchParams(window.location.search);
    const page = params.get('page') || '1';
    
    // Filtros atuais (exceto page)
    const filters = {};
    params.forEach((value, key) => {
      if (key !== 'page') filters[key] = value;
    });

    const queryString = buildQueryString(filters, page);
    const endpointQuery = Object.keys(filters).length ? `/filter?${queryString}` : `?${queryString}`;
    const response = await getJobs(endpointQuery);

    // O normalizeApiResponse retornará { vagas: [...], pagination: { ... } }
    const data = normalizeApiResponse(response);
    
    currentItems = data.vagas || data.items || (Array.isArray(data) ? data : []);
    renderJobs(currentItems);
    renderPagination(data.pagination || {});
    
    stopFaviconPulse();
  } catch (error) {
    jobsList.innerHTML = '';
    showToast(error.message, 'error');
    setFeedback(error.message, 'error');
  }
}

const searchForm = document.getElementById('searchForm');
const advancedFiltersForm = document.getElementById('advancedFiltersForm');
const toggleFiltersBtn = document.getElementById('toggleFilters');
const filterPanel = document.getElementById('filterPanel');
const clearFiltersBtn = document.getElementById('clearFiltersBtn');

toggleFiltersBtn?.addEventListener('click', () => {
  filterPanel.classList.toggle('open');
});

function updateUrlAndLoad() {
  const params = new URLSearchParams();
  Object.entries(lastFilters).forEach(([k, v]) => { if (v) params.set(k, v); });
  params.set('page', String(currentPage));

  const newUrl = `${window.location.pathname}${params.toString() ? '?' + params.toString() : ''}`;
  window.history.pushState({}, '', newUrl);

  loadJobs();
}

function applyFilters() {
  const params = new URLSearchParams();
  const q = document.getElementById('searchInput')?.value.trim();
  const area = document.getElementById('filterArea')?.value;
  const experiencia = document.getElementById('filterExperiencia')?.value;
  const tipo = document.getElementById('filterTipo')?.value;
  const salario_min = document.getElementById('filterSalario')?.value;

  if (q) params.set('q', q);
  if (area) params.set('area', area);
  if (experiencia) params.set('experiencia', experiencia);
  if (tipo) params.set('tipo', tipo);
  if (salario_min) params.set('salario_min', salario_min);

  currentPage = 1;
  lastFilters = Object.fromEntries(params.entries());
  updateUrlAndLoad();
}

searchForm?.addEventListener('submit', (event) => {
  event.preventDefault();
  applyFilters();
});

advancedFiltersForm?.addEventListener('submit', (event) => {
  event.preventDefault();
  applyFilters();
});

function clearFilters() {
  const searchInput = document.getElementById('searchInput');
  const filterArea = document.getElementById('filterArea');
  const filterExperiencia = document.getElementById('filterExperiencia');
  const filterTipo = document.getElementById('filterTipo');
  const filterSalario = document.getElementById('filterSalario');

  if (searchInput) searchInput.value = '';
  if (filterArea) filterArea.value = '';
  if (filterExperiencia) filterExperiencia.value = '';
  if (filterTipo) filterTipo.value = '';
  if (filterSalario) filterSalario.value = '';

  currentPage = 1;
  lastFilters = {};
  updateUrlAndLoad();
}

clearFiltersBtn?.addEventListener('click', clearFilters);

function syncFiltersFromUrl() {
  const params = new URLSearchParams(window.location.search);
  const filters = {};
  params.forEach((value, key) => {
    if (key === 'page') {
      currentPage = Number(value);
    } else {
      filters[key] = value;
      const input = searchForm?.querySelector(`[name="${key}"]`) || advancedFiltersForm?.querySelector(`[name="${key}"]`);
      if (input) input.value = value;
    }
  });
  lastFilters = filters;
}

syncFiltersFromUrl();

sortSelect?.addEventListener('change', () => {
  renderJobs(currentItems);
});

jobsList?.addEventListener('click', async (event) => {
  const applyButton = event.target.closest('button[data-apply-id]');
  if (applyButton) {
    await handleApply(applyButton, applyButton.dataset.applyId);
    return;
  }

  const favoriteButton = event.target.closest('button[data-favorite-id]');
  if (favoriteButton) {
    await handleFavorite(favoriteButton, favoriteButton.dataset.favoriteId);
  }
});

// Removido o listener antigo do pagination que usava IDs diferentes

await preloadUserStates();
await loadJobs();

