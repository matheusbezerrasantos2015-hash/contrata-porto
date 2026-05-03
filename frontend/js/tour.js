/**
 * ContrataPorto - Tour Interativo (Shepherd.js)
 * Guia amigável para novos candidatos com foco em acessibilidade e mobile.
 */

const tourKey = 'contrataporto_tour_done';
const isMobile = window.innerWidth < 768;

// 1. Estilos customizados (Design System + Mobile fixes)
const tourStyles = `
  .shepherd-element {
    border-radius: 12px !important;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04) !important;
    border: none !important;
    max-width: 360px !important;
    background: white !important;
    font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
  }
  .shepherd-header {
    background: #6C63FF !important;
    padding: 12px 16px !important;
  }
  .shepherd-title {
    color: white !important;
    font-size: 15px !important;
    font-weight: 700 !important;
  }
  .shepherd-text {
    padding: 16px !important;
    font-size: 14px !important;
    line-height: 1.6 !important;
    color: #374151 !important;
  }
  .shepherd-footer {
    padding: 12px 16px !important;
    display: flex !important;
    justify-content: flex-end !important;
    gap: 8px !important;
  }
  .shepherd-button {
    border-radius: 6px !important;
    padding: 8px 16px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    border: none !important;
    min-height: 40px !important;
  }
  .shepherd-button-primary {
    background: #6C63FF !important;
    color: white !important;
  }
  .shepherd-button-secondary {
    background: #F3F4F6 !important;
    color: #4B5563 !important;
  }
  
  /* Botão flutuante solicitado */
  #btn-tour {
    position: fixed;
    bottom: 24px;
    left: 24px;
    background: #6C63FF;
    color: white;
    border: none;
    border-radius: 24px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(108,99,255,0.35);
    z-index: 999;
    transition: transform 0.2s, opacity 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  #btn-tour:hover { transform: translateY(-2px); opacity: 0.9; }
  
  @media (max-width: 480px) {
    .shepherd-element { max-width: 92vw !important; }
    #btn-tour { bottom: 16px; left: 16px; padding: 8px 14px; font-size: 12px; }
    .shepherd-button { min-height: 44px !important; } /* Acessibilidade toque */
  }
`;

const styleEl = document.createElement('style');
styleEl.textContent = tourStyles;
document.head.appendChild(styleEl);

// 2. Criação do Tour
function criarTour() {
  const tour = new Shepherd.Tour({
    useModalOverlay: true,
    defaultStepOptions: {
      cancelIcon: { enabled: true },
      scrollTo: { behavior: 'smooth', block: 'center' },
      popperOptions: {
        modifiers: [
          { name: 'offset', options: { offset: [0, 12] } },
          { name: 'preventOverflow', options: { padding: 16 } }
        ]
      }
    }
  });

  // Passo 1: Boas-vindas
  tour.addStep({
    id: 'welcome',
    title: "Bem-vindo ao ContrataPorto! 👋",
    text: "Somos o portal de empregos de Porto Ferreira. Veja as vagas disponíveis, candidate-se e acompanhe tudo por aqui. Vamos dar uma voltinha?",
    buttons: [
      { text: "Pular tour", classes: "shepherd-button-secondary", action: tour.cancel },
      { text: "Começar →", classes: "shepherd-button-primary", action: tour.next }
    ]
  });

  // Passo 2: Busca
  tour.addStep({
    id: 'search',
    attachTo: { element: '#searchInput', on: isMobile ? 'bottom' : 'right' },
    beforeShowPromise: () => checkElement('#searchInput'),
    title: "Encontre sua vaga 🔍",
    text: "Digite o nome do cargo ou empresa que procura. Você também pode filtrar por área, salário e tipo de contrato.",
    buttons: [
      { text: "Anterior", classes: "shepherd-button-secondary", action: tour.back },
      { text: "Próximo", classes: "shepherd-button-primary", action: tour.next }
    ]
  });

  // Passo 3: Card de Vaga
  tour.addStep({
    id: 'jobs',
    attachTo: { element: '.job-card:first-child', on: 'top' },
    beforeShowPromise: () => checkElement('.job-card:first-child'),
    title: "Vagas disponíveis 💼",
    text: "Clique em qualquer vaga para ver todos os detalhes: salário, requisitos, benefícios e como se candidatar.",
    buttons: [
      { text: "Anterior", classes: "shepherd-button-secondary", action: tour.back },
      { text: "Próximo", classes: "shepherd-button-primary", action: tour.next }
    ]
  });

  // Passo 4: Botão de Login/Cadastro
  tour.addStep({
    id: 'login',
    attachTo: { element: '#loginBtn', on: isMobile ? 'bottom' : 'left' },
    beforeShowPromise: () => checkElement('#loginBtn'),
    title: "Crie sua conta grátis ✅",
    text: "É rápido e gratuito! Com sua conta você se candidata, salva vagas favoritas e acompanha o processo seletivo.",
    buttons: [
      { text: "Anterior", classes: "shepherd-button-secondary", action: tour.back },
      { text: "Próximo", classes: "shepherd-button-primary", action: tour.next }
    ]
  });

  // Passo 5: Finalização
  tour.addStep({
    id: 'finish',
    title: "Tudo certo! 🎉",
    text: "Agora você já sabe como usar o ContrataPorto. Boa sorte nas candidaturas!",
    buttons: [
      { text: "Explorar vagas", classes: "shepherd-button-primary", action: tour.complete }
    ]
  });

  // Eventos para salvar progresso
  const setDone = () => localStorage.setItem(tourKey, '1');
  tour.on('complete', setDone);
  tour.on('cancel', setDone);

  return tour;
}

// 3. Funções de Controle
async function checkElement(selector) {
  return new Promise(resolve => {
    const el = document.querySelector(selector);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      setTimeout(resolve, 500); // Aguarda scroll
    } else {
      // Se não existe, podemos pular este passo chamando next() no Shepherd
      // Mas o Shepherd espera que o resolve() aconteça para mostrar o step.
      // Aqui simplesmente resolvemos para o Shepherd tentar mostrar (e talvez falhar graciosamente)
      // ou pulamos logicamente no script se necessário.
      resolve();
    }
  });
}

function initTour() {
  // Garante que pelo menos o básico existe
  const elementosBase = ['#searchInput', '#jobsList'];
  const existeBase = elementosBase.some(sel => document.querySelector(sel) !== null);
  if (!existeBase) return null;

  return criarTour();
}

export function startTourManual() {
  localStorage.removeItem(tourKey);
  const tour = initTour();
  if (tour) tour.start();
}

// Expõe globalmente para o onclick ou scripts simples
window.startTourManual = startTourManual;

function autoStartTour() {
  // Não inicia se estiver logado (geralmente candidatos já conhecem o fluxo)
  if (localStorage.getItem('token')) return;

  if (!localStorage.getItem(tourKey)) {
    // Aguarda vagas carregarem (vêm via AJAX no jobs.js)
    setTimeout(() => {
      const tour = initTour();
      if (tour) tour.start();
    }, 2000); 
  }
}

// 4. Inicialização
document.addEventListener('DOMContentLoaded', () => {
  autoStartTour();
  
  // Listener para o botão
  document.getElementById('btn-tour')?.addEventListener('click', startTourManual);
});
