import { escapeHTML } from './utils.js';

const toastRoot = (() => {
  let root = document.getElementById('toastRoot');
  if (!root) {
    root = document.createElement('div');
    root.id = 'toastRoot';
    root.className = 'toast-root';
    document.body.appendChild(root);
  }
  return root;
})();

const globalLoader = (() => {
  let loader = document.getElementById('globalLoader');
  if (!loader) {
    loader = document.createElement('div');
    loader.id = 'globalLoader';
    loader.className = 'global-loader hidden';
    loader.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(loader);
  }
  return loader;
})();

export function showLoader() {
  globalLoader.classList.remove('hidden');
}

export function hideLoader() {
  globalLoader.classList.add('hidden');
}

export function setButtonLoading(button, loading = true, label = 'Processando...') {
  if (!button) return;
  if (loading) {
    button.dataset.originalText = button.textContent;
    button.textContent = label;
    button.disabled = true;
  } else {
    button.textContent = button.dataset.originalText || button.textContent;
    button.disabled = false;
  }
}

export async function withGlobalLoader(callback) {
  showLoader();
  try {
    return await callback();
  } finally {
    hideLoader();
  }
}

export function showToast(message, type = 'info') {
  const item = document.createElement('div');
  item.className = `toast toast-${type}`;
  
  const icons = {
    success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>',
    error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
    info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
  };

  // Ícones são literais HTML estáticos (sem dados do usuário)
  item.innerHTML = `
    <span class="toast-icon">${icons[type] || icons.info}</span>
    <span class="toast-message"></span>
  `;
  // Mensagem inserida via textContent para prevenir XSS
  item.querySelector('.toast-message').textContent = message;

  toastRoot.appendChild(item);

  setTimeout(() => {
    item.style.opacity = '0';
    item.style.transform = 'translateY(12px) scale(0.95)';
    setTimeout(() => item.remove(), 400);
  }, 4000);
}

export function statusBadge(status = '') {
  const normalized = String(status).toUpperCase();
  const map = {
    ENVIADA: { label: 'Em fila', className: 'status-enviada' },
    EM_ANALISE: { label: 'Em análise', className: 'status-em-analise' },
    APROVADA: { label: 'Aprovado! 🎉', className: 'status-aprovada' },
    RECUSADA: { label: 'Não aprovado', className: 'status-recusada' },
    ATIVA: { label: 'Ativa', className: 'status-active' },
    PAUSADA: { label: 'Pausada', className: 'status-paused' }
  };

  const statusConfig = map[normalized] || { label: normalized || 'Indefinido', className: 'status-default' };
  return `<span class="status-badge ${statusConfig.className}">${statusConfig.label}</span>`;
}

export function renderSkeleton(container, type = 'card', count = 3) {
  if (!container) return;
  const logoLoader = `<div class="skeleton-logo-loader" aria-hidden="true"><img src="/ContrataPorto/frontend/assets/favicon.png" alt="" class="skeleton-logo-spin"></div>`;
  let html = '';

  if (type === 'card') {
    html = logoLoader + Array.from({ length: count }).map(() => '<div class="skeleton-card"></div>').join('');
  } else if (type === 'detail') {
    html = `
      <div class="skeleton" style="height: 40px; width: 60%; margin-bottom: 12px;"></div>
      <div class="skeleton" style="height: 24px; width: 40%; margin-bottom: 24px;"></div>
      <div class="skeleton" style="height: 100px; width: 100%; margin-bottom: 12px;"></div>
      <div class="skeleton" style="height: 100px; width: 100%;"></div>
    `;
  }
  container.innerHTML = html;
}

export function confirmAction(message) {
  return window.confirm(message);
}

export function renderEmptyState(container, { title, message, ctaText, ctaHref, ctaId }) {
  if (!container) return;

  // Estrutura estática — dados dinâmicos inseridos via textContent (sem risco de XSS)
  const wrapper = document.createElement('div');
  wrapper.className = 'empty-state';

  const img = document.createElement('img');
  img.src = '../assets/favicon.png';
  img.alt = '';
  img.className = 'empty-state-icon';
  wrapper.appendChild(img);

  const pTitle = document.createElement('p');
  pTitle.className = 'empty-state-title empty-title';
  pTitle.textContent = title || 'Nenhum resultado';
  wrapper.appendChild(pTitle);

  const pMsg = document.createElement('p');
  pMsg.className = 'empty-state-subtitle empty-message';
  pMsg.textContent = message || 'Tente ajustar os filtros ou buscar novamente.';
  wrapper.appendChild(pMsg);

  if (ctaText && ctaHref) {
    const a = document.createElement('a');
    a.href = ctaHref;           // href é sempre URL interna controlada pelo código
    a.className = 'btn btn-primary';
    a.style.marginTop = '1rem';
    a.textContent = ctaText;
    wrapper.appendChild(a);
  }

  if (ctaText && ctaId) {
    const btn = document.createElement('button');
    btn.id = ctaId;
    btn.className = 'btn btn-primary';
    btn.style.marginTop = '1rem';
    btn.textContent = ctaText;
    wrapper.appendChild(btn);
  }

  container.innerHTML = '';
  container.appendChild(wrapper);
}
