import API, { API_URL, createJob, getJobApplications, getApplicationById, getJobs, updateApplicationStatus, updateJob, toggleJobStatus, concludeJob, deleteJob, normalizeApiResponse } from './api.js';
import { getAuthState, requireAuth, isAuthenticated, redirectToLogin } from './auth.js';
import { confirmAction, renderSkeleton, setButtonLoading, showToast, statusBadge, renderEmptyState } from './ui.js';
import { stopFaviconPulse } from './layout.js';
import { escapeHTML } from './utils.js';

requireAuth({ role: 'empresa' });

const jobsContainer = document.getElementById('companyJobs');
const candidatesContainer = document.getElementById('candidates');
const jobForm = document.getElementById('jobForm');
const feedback = document.getElementById('feedback');

// Modal Elements
const editModal = document.getElementById('editJobModal');
const editForm = document.getElementById('editJobForm');
const closeEditBtn = document.getElementById('closeEditModalBtn');

const candidateModal = document.getElementById('candidateModal');
const candidateModalContent = document.getElementById('candidateModalContent');
const closeCandidateBtn = document.getElementById('closeCandidateModalBtn');

let currentJobs = [];
let activeJobId = null;

function setFeedback(message, type = '') {
  feedback.textContent = message;
  feedback.className = `message ${type}`.trim();
}

function renderCompanyJobs(jobs) {
  jobsContainer.innerHTML = '';

  if (!jobs.length) {
    renderEmptyState(jobsContainer, {
      icon: '<i class="fa-solid fa-folder-open"></i>',
      title: 'Sem vagas',
      message: 'Publique sua primeira vaga.'
    });
    return;
  }

  jobs.forEach((job) => {
    const item = document.createElement('div');
    item.className = 'sidebar-vaga-item';
    const isPaused = job.status === 'PAUSADA';
    const isConcluded = job.status === 'CONCLUIDA';
    const statusText = isPaused ? 'Ativar' : 'Pausar';
    
    item.innerHTML = `
      <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: var(--space-2);">
        <div>
          <strong style="display: block; font-size: var(--font-sm);">${escapeHTML(job.titulo)}</strong>
          <span style="font-size: var(--font-xs); color: var(--slate-500);">${escapeHTML(job.cargo)}</span>
        </div>
        ${statusBadge(job.status)}
      </div>
      
      <div style="display: flex; gap: var(--space-1); margin-top: var(--space-2);">
        <button class="btn btn-ghost btn-sm" data-edit-job="${job.id}" title="Editar">
          <i class="fa-solid fa-pen"></i>
        </button>
        <button class="btn btn-ghost btn-sm" data-toggle-job="${job.id}" title="${statusText}" ${isConcluded ? 'disabled' : ''}>
          <i class="fa-solid ${isPaused ? 'fa-play' : 'fa-pause'}"></i>
        </button>
        <button class="btn btn-ghost btn-sm" data-conclude-job="${job.id}" title="Concluir" ${isConcluded ? 'disabled' : ''}>
          <i class="fa-solid fa-check"></i>
        </button>
        <button class="btn btn-ghost btn-sm" data-delete-job="${job.id}" title="Excluir" style="color: var(--color-danger);">
          <i class="fa-solid fa-trash"></i>
        </button>
      </div>

      <button class="btn btn-primary btn-block btn-sm" style="margin-top: var(--space-2);" onclick="abrirDrawerCandidatos(${job.id}, '${escapeHTML(job.titulo).replace(/'/g, "\\'")}')">
        Ver Candidatos (${job.applications_count || 0})
      </button>
    `;
    jobsContainer.appendChild(item);
  });
}

function abrirDrawerCandidatos(vagaId, vagaTitulo) {
    const overlay = document.getElementById('candidatos-overlay');
    const drawer  = document.getElementById('candidatos-drawer');
    const titulo  = document.getElementById('drawer-titulo');
    const conteudo = document.getElementById('drawer-conteudo');

    activeJobId = vagaId;
    if (titulo) titulo.textContent = 'Candidatos — ' + vagaTitulo;
    if (conteudo) conteudo.innerHTML = '<p style="color:#888; text-align:center; margin-top:40px;">Carregando...</p>';

    if (overlay) overlay.style.display = 'block';
    if (drawer) drawer.style.transform = 'translateX(0)';

    const token = localStorage.getItem('token');
    fetch(`${API_URL}/jobs/${vagaId}/applications`, {
        headers: { 'Authorization': 'Bearer ' + token }
    })
    .then(r => r.json())
    .then(response => {
        const data = normalizeApiResponse(response);
        const apps = Array.isArray(data) ? data : (data.items || []);
        
        if (!apps.length) {
            conteudo.innerHTML = '<p style="color:#888; text-align:center; margin-top:40px;">Nenhum candidato ainda.</p>';
            return;
        }

        const badges = {
            'PENDENTE':   { bg:'#f0f0f0', color:'#555' },
            'EM_ANALISE': { bg:'#e3f0ff', color:'#1565c0' },
            'APROVADO':   { bg:'#e8f5e9', color:'#2e7d32' },
            'RECUSADO':   { bg:'#fce4e4', color:'#c62828' },
        };

        conteudo.innerHTML = apps.map(app => {
            const status = (app.status || 'PENDENTE').toUpperCase();
            const badge = badges[status] || badges['PENDENTE'];
            const nome = app.candidato_nome || app.name || app.nome || 'Candidato';
            
            return `
            <div style="background:#f9f9f9; border-radius:12px; padding:16px; margin-bottom:12px; border:1px solid #eee;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <p style="margin:0; font-weight:600; color:#1a1a1a;">${escapeHTML(nome)}</p>
                        <p style="margin:4px 0 8px; font-size:13px; color:#888;">${escapeHTML(app.candidato_email || '')}</p>
                    </div>
                    <span style="background:${badge.bg}; color:${badge.color};
                                 padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                        ${status}
                    </span>
                </div>
                <div style="display:flex; gap:8px; margin-top:8px;">
                    <button onclick="verPerfilCandidato(${app.id})" style="
                        flex:1; padding:8px; background:#f0ebff; color:#6c3fc5;
                        border:1.5px solid #d0c4f0; border-radius:8px; cursor:pointer;
                        font-size:13px; font-weight:600;">
                        Ver Perfil
                    </button>
                    <button onclick="abrirModalCurriculo(${app.id}, '${nome.replace(/'/g, "\\'")}')" style="
                        flex:1; padding:8px; background:#6c3fc5; color:#fff;
                        border:none; border-radius:8px; cursor:pointer;
                        font-size:13px; font-weight:600;">
                        Ver Currículo
                    </button>
                </div>
            </div>`;
        }).join('');
    })
    .catch((err) => {
        console.error("[DRAWER_ERR]", err);
        conteudo.innerHTML = '<p style="color:#e53935; text-align:center; margin-top:40px;">Erro ao carregar candidatos.</p>';
    });
}

function fecharDrawer() {
    const drawer = document.getElementById('candidatos-drawer');
    const overlay = document.getElementById('candidatos-overlay');
    if (drawer) drawer.style.transform = 'translateX(100%)';
    if (overlay) overlay.style.display = 'none';
}

// Lógica do Modal de Currículo
async function abrirModalCurriculo(applicationId, candidatoNome) {
  try {
    const response = await getApplicationById(applicationId);
    const item = normalizeApiResponse(response);

    if (item.curriculo_url) {
      document.getElementById('curriculo-modal-nome').textContent = candidatoNome;
      const btnVer = document.getElementById('btn-ver-curriculo');
      const btnBaixar = document.getElementById('btn-baixar-curriculo');
      
      btnVer.href = item.curriculo_url;
      btnBaixar.href = item.curriculo_url + (item.curriculo_url.includes('?') ? '&' : '?') + 'fl_attachment=true';
      
      document.getElementById('curriculo-modal-overlay').style.display = 'flex';
    } else {
      showToast('Este candidato não anexou currículo', 'info');
    }
  } catch (error) {
    showToast(error.message || 'Erro ao carregar currículo', 'error');
  }
}

function fecharModalCurriculo() {
  const modal = document.getElementById('curriculo-modal-overlay');
  if (modal) modal.style.display = 'none';
}

// Expor para o escopo global
window.abrirDrawerCandidatos = abrirDrawerCandidatos;
window.fecharDrawer = fecharDrawer;
window.verPerfilCandidato = openCandidateModal;
window.abrirModalCurriculo = abrirModalCurriculo;
window.fecharModalCurriculo = fecharModalCurriculo;

// Listeners de UI
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btn-fechar-drawer')?.addEventListener('click', fecharDrawer);
    document.getElementById('candidatos-overlay')?.addEventListener('click', fecharDrawer);
});
document.getElementById('btn-fechar-modal-curriculo')?.addEventListener('click', fecharModalCurriculo);

async function loadCompanyJobs() {
  if (!isAuthenticated()) { redirectToLogin(); return; }

  try {
    renderSkeleton(jobsContainer, 'card', 2);
    const query = '/my-company';
    const response = await getJobs(query);
    const data = normalizeApiResponse(response);
    currentJobs = data;
    renderCompanyJobs(data);
    stopFaviconPulse();
  } catch (error) {
    jobsContainer.innerHTML = '';
    showToast(error.message, 'error');
    setFeedback(error.message, 'error');
  }
}



function openEditModal(job) {
  editForm.id.value = job.id;
  editForm.titulo.value = job.titulo;
  editForm.cargo.value = job.cargo || '';
  editForm.descricao.value = job.descricao || '';
  editForm.experiencia.value = job.experiencia || 'SEM_EXPERIENCIA';
  editForm.requisitos.value = job.requisitos || '';
  editForm.diferenciais.value = job.diferenciais || '';
  editForm.tipo_vaga.value = job.tipo_vaga || 'PRESENCIAL';
  editForm.tipo_contrato.value = job.tipo_contrato;
  editForm.carga_horaria.value = job.carga_horaria || '';
  editForm.salario_min.value = job.salario_min || '';
  editForm.salario_max.value = job.salario_max || '';
  editForm.beneficios.value = job.beneficios || '';
  
  editModal.style.display = 'flex';
}

closeEditBtn?.addEventListener('click', () => {
  editModal.style.display = 'none';
});

closeCandidateBtn?.addEventListener('click', () => {
  candidateModal.style.display = 'none';
});

function getInitials(name = '') {
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
}

function formatWhatsAppLink(telefone) {
  const numero = telefone.replace(/\D/g, '');
  const completo = numero.startsWith('55') ? numero : '55' + numero;
  return 'https://wa.me/' + completo;
}

async function openCandidateModal(id) {
  candidateModal.style.display = 'flex';
  candidateModalContent.innerHTML = `
    <div style="text-align: center; padding: var(--space-4);">
      <div class="skeleton" style="height: 80px; width: 80px; border-radius: 50%; margin: 0 auto;"></div>
      <div class="skeleton" style="height: 24px; width: 50%; margin: 16px auto;"></div>
    </div>
  `;

  try {
    const response = await getApplicationById(id);
    const item = normalizeApiResponse(response);
    const initials = getInitials(item.candidato_nome);
    
    // Dados de texto do candidato são escapados para prevenir XSS
    // URLs (linkedin, portfolio) são usadas apenas em href e validadas pelo browser
    candidateModalContent.innerHTML = `
      <div style="text-align: center; padding: var(--space-2);">
        <div style="width: 72px; height: 72px; background: var(--slate-100); color: var(--slate-600); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-3); font-size: 24px; font-weight: 700; border: 2px solid var(--white); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
          ${escapeHTML(initials)}
        </div>
        <h2 style="font-size: var(--font-lg); font-weight: 700; color: var(--slate-900);">${escapeHTML(item.candidato_nome)}</h2>
        <div style="color: var(--slate-500); font-size: var(--font-sm); display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 4px;">
          <a href="mailto:${escapeHTML(item.candidato_email)}" style="color: inherit; text-decoration: none;" title="Enviar e-mail">${escapeHTML(item.candidato_email)}</a>
        </div>
        <div style="margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap;">
          ${item.telefone ? `
            <span style="font-size: var(--font-sm); color: var(--slate-600);">${escapeHTML(item.telefone)}</span>
            <a href="${formatWhatsAppLink(item.telefone)}"
               target="_blank"
               rel="noopener noreferrer"
               class="btn-whatsapp"
               title="Abrir conversa no WhatsApp">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                <path d="M11.999 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.978-1.393A9.96 9.96 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 11.999 2zm.001 18a7.96 7.96 0 0 1-4.073-1.114l-.292-.174-3.038.849.854-3.117-.191-.302A7.96 7.96 0 0 1 4 12c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8z"/>
              </svg>
              WhatsApp
            </a>
          ` : `
            <span style="font-size: var(--font-xs); color: var(--slate-400); font-style: italic;">
              Sem telefone cadastrado — entre em contato pelo
              <a href="mailto:${escapeHTML(item.candidato_email)}" style="color: var(--brand-500); text-decoration: underline;">e-mail</a>
            </span>
          `}
        </div>
      </div>

      <div style="margin-top: var(--space-4); border-top: 1px solid var(--slate-100); padding-top: var(--space-4);">
        <h3 style="font-size: var(--font-sm); text-transform: uppercase; color: var(--slate-500); letter-spacing: 0.05em; margin-bottom: var(--space-2);">Carta de Apresentação</h3>
        <p style="color: var(--slate-700); line-height: 1.6; background: var(--slate-50); padding: var(--space-3); border-radius: 8px; font-size: var(--font-sm);">
          ${escapeHTML(item.mensagem || 'Nenhuma mensagem enviada.')}
        </p>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-3); margin-top: var(--space-4);">
        <div>
          <h3 style="font-size: var(--font-xs); text-transform: uppercase; color: var(--slate-500); margin-bottom: 4px;">LinkedIn</h3>
          ${item.linkedin ? `<a href="${escapeHTML(item.linkedin)}" target="_blank" class="btn btn-ghost" style="width: 100%; justify-content: flex-start; padding: 0 8px; font-size: var(--font-xs); height: 32px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Acessar Perfil</a>` : '<span style="font-size: var(--font-xs); color: var(--slate-400);">Não informado</span>'}
        </div>
        <div>
          <h3 style="font-size: var(--font-xs); text-transform: uppercase; color: var(--slate-500); margin-bottom: 4px;">Portfólio</h3>
          ${item.portfolio ? `<a href="${escapeHTML(item.portfolio)}" target="_blank" class="btn btn-ghost" style="width: 100%; justify-content: flex-start; padding: 0 8px; font-size: var(--font-xs); height: 32px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Ver Portfólio</a>` : '<span style="font-size: var(--font-xs); color: var(--slate-400);">Não informado</span>'}
        </div>
      </div>

      <div style="margin-top: var(--space-4);">
        ${item.curriculo_path ? `
          <a href="/api/applications/${item.id}/curriculo" download class="btn btn-primary btn-block" style="gap: 8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Baixar Currículo (PDF)
          </a>
        ` : `
          <div style="background: var(--slate-50); color: var(--slate-400); text-align: center; padding: var(--space-3); border-radius: 8px; border: 1px dashed var(--slate-200); font-size: var(--font-sm);">Curriculo não anexado</div>
        `}
      </div>

      <div style="margin-top: var(--space-5); display: flex; align-items: center; justify-content: space-between; gap: var(--space-2); padding-top: var(--space-4); border-top: 1px solid var(--slate-100);">
        <div style="display: flex; flex-direction: column; gap: 4px;">
           <span style="font-size: var(--font-xs); color: var(--slate-400); text-transform: uppercase;">Status Atual</span>
           ${statusBadge(item.status)}
        </div>
        <div style="display: flex; gap: var(--space-1);">
          <button class="btn btn-ghost" data-update-id="${item.id}" data-status="EM_ANALISE" style="padding: 0 12px; height: 36px; font-size: var(--font-xs);">Analisar</button>
          <button class="btn btn-secondary" data-update-id="${item.id}" data-status="APROVADO" style="padding: 0 12px; height: 36px; font-size: var(--font-xs);">Aprovar</button>
          <button class="btn btn-ghost" data-update-id="${item.id}" data-status="RECUSADO" style="padding: 0 12px; height: 36px; font-size: var(--font-xs); color: var(--color-danger);">Recusar</button>
        </div>
      </div>
    `;
  } catch (error) {
    showToast(error.message, 'error');
    candidateModal.style.display = 'none';
  }
}

editForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const submitBtn = editForm.querySelector('button[type="submit"]');
  const id = editForm.id.value;
  
  const payload = {
    titulo: editForm.titulo.value.trim(),
    cargo: editForm.cargo.value.trim(),
    descricao: editForm.descricao.value.trim(),
    experiencia: editForm.experiencia.value,
    requisitos: editForm.requisitos.value.trim(),
    diferenciais: editForm.diferenciais.value.trim(),
    tipo_vaga: editForm.tipo_vaga.value,
    tipo_contrato: editForm.tipo_contrato.value,
    carga_horaria: editForm.carga_horaria.value.trim(),
    salario_min: editForm.salario_min.value || null,
    salario_max: editForm.salario_max.value || null,
    beneficios: editForm.beneficios.value.trim()
  };

  try {
    setButtonLoading(submitBtn, true, 'Salvando...');
    await updateJob(id, payload);
    showToast('Vaga atualizada com sucesso.', 'success');
    editModal.style.display = 'none';
    loadCompanyJobs();
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    setButtonLoading(submitBtn, false);
  }
});

jobForm?.addEventListener('submit', async (event) => {
  event.preventDefault();
  const submitBtn = jobForm.querySelector('button[type="submit"]');

  const payload = {
    titulo: jobForm.titulo.value.trim(),
    cargo: jobForm.cargo.value.trim(),
    descricao: jobForm.descricao.value.trim(),
    experiencia: jobForm.experiencia.value,
    requisitos: jobForm.requisitos.value.trim(),
    diferenciais: jobForm.diferenciais.value.trim(),
    tipo_vaga: jobForm.tipo_vaga.value,
    tipo_contrato: jobForm.tipo_contrato.value,
    carga_horaria: jobForm.carga_horaria.value.trim(),
    salario_min: jobForm.salario_min.value || null,
    salario_max: jobForm.salario_max.value || null,
    beneficios: jobForm.beneficios.value.trim()
  };

  try {
    setFeedback('Publicando vaga...');
    setButtonLoading(submitBtn, true, 'Publicando...');
    await createJob(payload);
    showToast('Vaga criada com sucesso.', 'success');
    setFeedback('Vaga criada com sucesso.', 'success');
    jobForm.reset();
    await loadCompanyJobs();
  } catch (error) {
    showToast(error.message, 'error');
    setFeedback(error.message, 'error');
  } finally {
    setButtonLoading(submitBtn, false);
  }
});

jobsContainer?.addEventListener('click', async (event) => {
  const toggleBtn = event.target.closest('button[data-toggle-job]');
  if (toggleBtn) {
    const id = Number(toggleBtn.dataset.toggleJob);
    if (!confirmAction('Alterar status desta vaga?')) return;
    try {
      setButtonLoading(toggleBtn, true, 'Aguarde...');
      await toggleJobStatus(id);
      showToast('Status alterado com sucesso.', 'success');
      loadCompanyJobs();
    } catch (error) {
      showToast(error.message, 'error');
      setButtonLoading(toggleBtn, false);
    }
    return;
  }

  const concludeBtn = event.target.closest('button[data-conclude-job]');
  if (concludeBtn) {
    const id = Number(concludeBtn.dataset.concludeJob);
    if (!confirmAction('Após concluída, a vaga ficará visível por mais 3 dias e será removida automaticamente. Confirmar?')) return;
    try {
      setButtonLoading(concludeBtn, true, 'Aguarde...');
      await concludeJob(id);
      showToast('Vaga concluída com sucesso.', 'success');
      loadCompanyJobs();
    } catch (error) {
      showToast(error.message, 'error');
      setButtonLoading(concludeBtn, false);
    }
    return;
  }

  const deleteBtn = event.target.closest('button[data-delete-job]');
  if (deleteBtn) {
    const id = Number(deleteBtn.dataset.deleteJob);
    if (!confirmAction('Esta ação é irreversível. Todos os candidatos serão removidos. Confirmar exclusão?')) return;
    try {
      setButtonLoading(deleteBtn, true, 'Deletando...');
      await deleteJob(id);
      showToast('Vaga e candidaturas deletadas com sucesso.', 'success');
      loadCompanyJobs();
    } catch (error) {
      showToast(error.message, 'error');
      setButtonLoading(deleteBtn, false);
    }
    return;
  }

  const editBtn = event.target.closest('button[data-edit-job]');
  if (editBtn) {
    const id = Number(editBtn.dataset.editJob);
    const job = currentJobs.find(j => Number(j.id) === id);
    if (job) openEditModal(job);
    return;
  }

  const button = event.target.closest('button[data-job-id]');
  if (button) {
    // Agora usando onclick direto, mas mantemos o listener vazio ou tratamos se necessário
  }
});

// Delegação para fechar modal currículo ao clicar fora (opcional se já tiver o listener acima)
document.addEventListener('click', (event) => {
    if (event.target.id === 'curriculo-modal-overlay') {
        fecharModalCurriculo();
    }
});

// Delegação para abertura de modal de candidatos (centralizada)
document.addEventListener('click', (event) => {
  // Modal de Perfil
  const viewCandidateBtn = event.target.closest('button[data-view-candidate]');
  if (viewCandidateBtn) {
    const id = Number(viewCandidateBtn.dataset.viewCandidate);
    openCandidateModal(id);
    return;
  }

  // Modal de Currículo
  const viewResumeBtn = event.target.closest('button[data-view-resume]');
  if (viewResumeBtn) {
    const id = Number(viewResumeBtn.dataset.viewResume);
    const name = viewResumeBtn.dataset.candidateName;
    abrirModalCurriculo(id, name);
    return;
  }

  // Fechar Modal Currículo
  if (event.target.id === 'curriculo-modal-overlay' || event.target.id === 'btn-fechar-modal-curriculo') {
    fecharModalCurriculo();
  }
});

candidateModalContent?.addEventListener('click', async (event) => {
  const actionBtn = event.target.closest('button[data-update-id]');
  if (!actionBtn) return;

  const id = Number(actionBtn.dataset.updateId);
  const status = actionBtn.dataset.status;

  if (!confirmAction('Confirma a atualização de status da candidatura?')) {
    return;
  }

  try {
    setButtonLoading(actionBtn, true, 'Atualizando...');
    await updateApplicationStatus(id, status);
    showToast('Status atualizado com sucesso.', 'success');
    
    // Refresh modal content to show new status
    await openCandidateModal(id);

      // Refresh underlying list (drawer)
      if (activeJobId) {
        const job = currentJobs.find(j => j.id === activeJobId);
        abrirDrawerCandidatos(activeJobId, job ? job.titulo : 'Vaga');
      }
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    setButtonLoading(actionBtn, false);
  }
});

loadCompanyJobs();
