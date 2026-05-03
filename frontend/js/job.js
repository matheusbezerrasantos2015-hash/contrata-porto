import { applyToJob, getFavorites, getJobById, getMyApplications, removeFavorite, saveFavorite, normalizeApiResponse } from './api.js';
import { isAuthenticated, redirectToLogin } from './auth.js';
import { renderSkeleton, setButtonLoading, showToast, withGlobalLoader } from './ui.js';
import { stopFaviconPulse } from './layout.js';

const jobContainer = document.getElementById('jobContainer');
const feedback = document.getElementById('feedback');
const applyButton = document.getElementById('applyButton');
const favoriteButton = document.getElementById('favoriteButton');

const mobileSnapCta = document.getElementById('mobileSnapCta');
const mobileApplyBtn = document.getElementById('mobileApplyBtn');
const shareBtn = document.getElementById('shareBtn');

let isApplied = false;
let favoriteId = null;

function setFeedback(message, type = '') {
  feedback.textContent = message;
  feedback.className = `message ${type}`.trim();
}

function getJobIdFromUrl() {
  const params = new URLSearchParams(window.location.search);
  return Number(params.get('id'));
}

async function preloadState(jobId) {
  if (!isAuthenticated()) return;

  try {
    const [applicationsResponse, favoritesResponse] = await Promise.all([
      getMyApplications(1, 100),
      getFavorites()
    ]);

    const appsData = normalizeApiResponse(applicationsResponse);
    // Backend retorna c.vaga_id AS job_id — fallback para vaga_id por compatibilidade
    isApplied = Array.isArray(appsData) && appsData.some((item) => Number(item.job_id ?? item.vaga_id) === jobId);

    const favsData = normalizeApiResponse(favoritesResponse);
    const fav = Array.isArray(favsData) ? favsData.find((item) => Number(item.vaga_id) === jobId) : null;
    favoriteId = fav ? Number(fav.vaga_id) : null;
  } catch (_) {
    // não bloqueia renderização principal
  }
}

function refreshButtons(jobId) {
  applyButton.dataset.jobId = String(jobId);
  favoriteButton.dataset.jobId = String(jobId);
  if (mobileApplyBtn) mobileApplyBtn.dataset.jobId = String(jobId);

  [applyButton, mobileApplyBtn].forEach(btn => {
    if (!btn) return;
    btn.disabled = isApplied;
    btn.textContent = isApplied ? 'Candidatado' : 'Candidatar-se';
  });

  favoriteButton.disabled = false;
  favoriteButton.textContent = favoriteId ? 'Salvo' : 'Salvar vaga';

  // Mostrar CTA mobile se estiver em tela pequena
  if (window.innerWidth < 768) {
    mobileSnapCta?.classList.remove('hidden');
  }
}

async function loadJob() {
  const jobId = getJobIdFromUrl();
  if (!jobId) {
    jobContainer.innerHTML = '<p>Vaga inválida.</p>';
    return;
  }

  try {
    renderSkeleton(jobContainer, 'detail');
    await preloadState(jobId);

    const response = await withGlobalLoader(() => getJobById(jobId));
    const job = normalizeApiResponse(response);

    document.title = `${job.titulo} | ContrataPorto`;

    jobContainer.innerHTML = `
      <div class="job-header-detailed section-card mb-4">
        <div class="company mb-1">${job.empresa_nome || 'Empresa Local'}</div>
        <h1 style="font-size: var(--font-3xl); font-weight: 800; margin-bottom: var(--space-1);">${job.titulo || 'Título não informado'}</h1>
        
        <div class="meta mb-3">
          <span>📍 ${job.cidade || 'Porto Ferreira'} - ${job.estado || 'SP'}</span>
          <span>•</span>
          <span>💼 ${job.tipo_contrato || 'CLT'} (${job.tipo_vaga || 'Presencial'})</span>
          <span>•</span>
          <span>📅 Publicada em ${job.publicada_em ? new Date(job.publicada_em).toLocaleDateString('pt-BR') : 'Recente'}</span>
        </div>

        <div class="chips">
          <span class="chip">${job.nivel || 'Nível não informado'}</span>
          <span class="chip">${job.area || 'Área não informada'}</span>
          <span class="chip">${job.experiencia ? job.experiencia.replace(/_/g, ' ') : 'Experiência não informada'}</span>
          ${job.carga_horaria ? `<span class="chip">🕒 ${job.carga_horaria}</span>` : ''}
        </div>
      </div>

      <div class="section-card mb-4">
        <h2 style="font-size: var(--font-xl); font-weight: 700; margin-bottom: var(--space-3);">Informações da Vaga</h2>
        <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
          <div class="info-item">
            <span style="font-size: var(--font-xs); color: var(--gray-500); display: block; text-transform: uppercase;">Salário</span>
            <span style="font-weight: 600; color: var(--brand-700);">
              ${(job.salario_min || job.salario_max) 
                ? `R$ ${Number(job.salario_min).toLocaleString('pt-BR')} ${job.salario_max ? ' - R$ ' + Number(job.salario_max).toLocaleString('pt-BR') : ''}` 
                : 'A combinar'}
            </span>
          </div>
          <div class="info-item">
            <span style="font-size: var(--font-xs); color: var(--gray-500); display: block; text-transform: uppercase;">Contrato</span>
            <span style="font-weight: 600;">${job.tipo_contrato || 'Informar na entrevista'}</span>
          </div>
        </div>
      </div>

      <div class="section-card mb-4">
        <h2 style="font-size: var(--font-xl); font-weight: 700; margin-bottom: var(--space-3);">Descrição da Vaga</h2>
        <div class="rich-text">
          ${(job.descricao || 'Nenhuma descrição detalhada fornecida.').replace(/\n/g, '<br>')}
        </div>
      </div>

      ${(job.requisitos || job.beneficios || job.diferenciais) ? `
      <div class="section-card grid md:grid-cols-2 gap-6">
        ${job.requisitos ? `
        <div>
          <h3 class="mb-2" style="font-weight: 700;">Requisitos</h3>
          <div class="rich-text">${job.requisitos.replace(/\n/g, '<br>')}</div>
        </div>` : ''}
        
        ${job.beneficios ? `
        <div>
          <h3 class="mb-2" style="font-weight: 700;">Benefícios</h3>
          <div class="rich-text">${job.beneficios.replace(/\n/g, '<br>')}</div>
        </div>` : ''}

        ${job.diferenciais ? `
        <div style="grid-column: 1 / -1; margin-top: 1rem; border-top: 1px solid var(--gray-100); padding-top: 1rem;">
          <h3 class="mb-2" style="font-weight: 700;">Diferenciais</h3>
          <div class="rich-text">${job.diferenciais.replace(/\n/g, '<br>')}</div>
        </div>` : ''}
      </div>` : ''}
    `;

    refreshButtons(job.id);
    stopFaviconPulse();

    // Configura o link do WhatsApp com dados reais da vaga
    const shareWhatsapp = document.getElementById('shareWhatsapp');
    if (shareWhatsapp) {
      const jobUrl = window.location.href;
      const text = encodeURIComponent(
        `Olha essa vaga que encontrei no ContrataPorto!\n\n` +
        `*${job.titulo}* — ${job.empresa_nome || 'Empresa Local'}\n` +
        jobUrl
      );
      shareWhatsapp.href = `https://wa.me/?text=${text}`;
    }


    const params = new URLSearchParams(window.location.search);
    if (params.get('apply') === 'true' && !isApplied && isAuthenticated()) {
      // TODO: reimplementar abertura automática de modal se necessário
    }
  } catch (error) {
    jobContainer.innerHTML = '';
    showToast(error.message, 'error');
    setFeedback(error.message, 'error');
  }
}

favoriteButton?.addEventListener('click', async () => {
  const jobId = getJobIdFromUrl();
  if (!isAuthenticated()) {
    redirectToLogin(window.location.pathname + window.location.search);
    return;
  }

  if (!favoriteId) {
    try {
      setButtonLoading(favoriteButton, true, 'Salvando...');
      await withGlobalLoader(() => saveFavorite(jobId));
      favoriteId = jobId;
      refreshButtons(jobId);
      showToast('Vaga salva', 'success');
    } catch (error) {
      showToast(error.message, 'error');
    } finally {
      setButtonLoading(favoriteButton, false);
    }
    return;
  }

  try {
    setButtonLoading(favoriteButton, true, 'Removendo...');
    await withGlobalLoader(() => removeFavorite(favoriteId));
    favoriteId = null;
    refreshButtons(jobId);
    showToast('Vaga removida', 'info');
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    setButtonLoading(favoriteButton, false);
  }
});

shareBtn?.addEventListener('click', async () => {
  const shareData = {
    title: document.title,
    text: 'Confira esta vaga no ContrataPorto!',
    url: window.location.href
  };

  try {
    if (navigator.share) {
      await navigator.share(shareData);
    } else {
      await navigator.clipboard.writeText(window.location.href);
      showToast('Link copiado para a área de transferência', 'success');
    }
  } catch (_) {
    // Usuário cancelou o share
  }
});

// ==========================================
// LÓGICA DO NOVO MODAL
// ==========================================

// Abrir modal
function openModal() {
  if (!isAuthenticated()) {
    redirectToLogin(window.location.pathname + window.location.search);
    return;
  }
  document.getElementById('applyModal').style.display = 'flex';
}

document.getElementById('applyButton')?.addEventListener('click', openModal);
document.getElementById('mobileApplyBtn')?.addEventListener('click', openModal);

// Fechar modal (botão × e botão Cancelar)
document.getElementById('closeModal').addEventListener('click', closeModal);
document.getElementById('cancelApply').addEventListener('click', closeModal);

// Fechar clicando no overlay
document.getElementById('applyModal').addEventListener('click', (e) => {
  if (e.target.id === 'applyModal') closeModal();
});

function closeModal() {
  document.getElementById('applyModal').style.display = 'none';
  document.getElementById('applyMessage').value = '';
  document.getElementById('applyLinkedin').value = '';
  document.getElementById('applyPortfolio').value = '';
  document.getElementById('applyPhone').value = '';
  document.getElementById('applyResume').value = '';
}

// Submeter candidatura
document.getElementById('confirmApply').addEventListener('click', async () => {
  const message = document.getElementById('applyMessage').value.trim();
  const linkedin = document.getElementById('applyLinkedin').value.trim();
  const portfolio = document.getElementById('applyPortfolio').value.trim();
  const phone = document.getElementById('applyPhone').value.trim();
  const resume = document.getElementById('applyResume').files[0];
  const jobId = getJobIdFromUrl();
  const submitBtn = document.getElementById('confirmApply');

  if (resume) {
    if (resume.type !== 'application/pdf') {
      showToast('Por favor, selecione um arquivo PDF.', 'error');
      return;
    }
    if (resume.size > 2 * 1024 * 1024) {
      showToast('O arquivo deve ter no máximo 2MB.', 'error');
      return;
    }
  }

  try {
    setButtonLoading(submitBtn, true, 'Enviando...');
    
    // Preparando dados da mesma forma que applyToJob espera
    const formData = new FormData();
    formData.append('vaga_id', jobId);
    formData.append('mensagem', message);
    formData.append('linkedin', linkedin);
    formData.append('portfolio', portfolio);
    formData.append('telefone', phone);
    if (resume) formData.append('curriculo', resume);

    await withGlobalLoader(() => applyToJob(formData));
    
    isApplied = true;
    closeModal();
    refreshButtons(jobId);
    showToast('Candidatura enviada com sucesso!', 'success');
  } catch (error) {
    showToast(error.message || 'Erro ao enviar candidatura', 'error');
  } finally {
    setButtonLoading(submitBtn, false);
  }
});

loadJob();
