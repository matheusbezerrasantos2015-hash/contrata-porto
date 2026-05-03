import { getAuthState, logoutAndRedirect } from './auth.js';

// ==========================================
// FAVICON PULSE (loading indicator)
// ==========================================
const _faviconLink = document.querySelector("link[rel='icon']");
const _originalFavicon = _faviconLink?.href ?? '';
let _faviconInterval = null;
let _faviconBright = true;

function startFaviconPulse() {
  if (!_faviconLink) return;
  _faviconInterval = setInterval(() => {
    _faviconBright = !_faviconBright;
    _faviconLink.href = _faviconBright
      ? _originalFavicon
      : _originalFavicon + '?dim=1';
  }, 600);
}

export function stopFaviconPulse() {
  if (_faviconInterval) {
    clearInterval(_faviconInterval);
    _faviconInterval = null;
    if (_faviconLink) _faviconLink.href = _originalFavicon;
  }
}

// Start on DOMContentLoaded, auto-stop on full load
document.addEventListener('DOMContentLoaded', startFaviconPulse);
window.addEventListener('load', () => setTimeout(stopFaviconPulse, 500));


async function loadComponent(targetId, path) {
  const target = document.getElementById(targetId);
  if (!target) return;

  try {
    const response = await fetch(path);
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    target.innerHTML = await response.text();
    return true;
  } catch (e) {
    console.warn(`Failed to load component ${targetId}:`, e);
    return false;
  }
}

function bindMobileMenu() {
  const toggle = document.getElementById('menuToggle');
  const nav = document.getElementById('navList');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', () => {
    nav.classList.toggle('open');
    toggle.classList.toggle('active');
  });
}

function bindAuthNav() {
  const auth = getAuthState();
  const loginBtn = document.getElementById('loginBtn');
  const userProfile = document.getElementById('userProfile');
  const navDashboard = document.getElementById('navDashboard');

  if (!auth.token) {
    if (userProfile) userProfile.classList.add('hidden');
    if (loginBtn) loginBtn.classList.remove('hidden');
    document.querySelectorAll('.nav-auth-only').forEach(el => el.classList.add('hidden'));
  } else {
    if (loginBtn) loginBtn.classList.add('hidden');
    if (userProfile) {
      userProfile.classList.remove('hidden');
      
      const avatar = document.getElementById('userAvatar');
      const nameEl = document.getElementById('headerUserName');
      const emailEl = document.getElementById('headerUserEmail');
      
      if (nameEl) nameEl.textContent = auth.nome_fantasia || auth.nome;
      if (emailEl) emailEl.textContent = auth.email;
      if (avatar) {
        const initials = (auth.nome_fantasia || auth.nome || 'U')
          .split(' ')
          .filter(Boolean)
          .map(n => n[0])
          .join('')
          .toUpperCase()
          .substring(0, 2);
        avatar.textContent = initials;
      }

      // Dropdown toggle logic
      userProfile.addEventListener('click', (e) => {
        e.stopPropagation();
        userProfile.classList.toggle('active');
      });

      document.addEventListener('click', () => userProfile.classList.remove('active'));
    }

    if (navDashboard) {
      navDashboard.href = auth.role === 'empresa' ? './dashboard.html' : './candidate-dashboard.html';
      navDashboard.textContent = auth.role === 'empresa' ? 'Recrutamento' : 'Minhas Vagas';
    }

    // Dynamic Settings Links
    const sLink = document.getElementById('settingsLink');
    const sLinkDrop = document.getElementById('settingsLinkDropdown');
    const settingsPath = auth.role === 'empresa' ? './settings-empresa.html' : './settings.html';
    if (sLink) sLink.href = settingsPath;
    if (sLinkDrop) sLinkDrop.href = settingsPath;

    const logoutBtn = document.getElementById('logoutBtn');
    logoutBtn?.addEventListener('click', async (e) => {
      e.preventDefault();
      await logoutAndRedirect();
    });
  }
}

(async function initLayout() {
  await Promise.all([
    loadComponent('appHeader', '../components/header.html'),
    loadComponent('appFooter', '../components/footer.html')
  ]);
  
  bindMobileMenu();
  bindAuthNav();
  
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('../sw.js')
      .then(() => console.log('[SW] Registrado'))
      .catch(err => console.error('[SW] Erro:', err));
  }
})();
