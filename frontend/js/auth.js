import { logout as logoutRequest } from './api.js';


function decodeJwtPayload(token) {
  try {
    const [, payload] = token.split('.');
    if (!payload) return {};
    const normalized = payload.replace(/-/g, '+').replace(/_/g, '/');
    const decoded = JSON.parse(atob(normalized));
    
    // Checagem de expiração client-side
    if (decoded.exp && (Date.now() / 1000) > decoded.exp) {
      localStorage.removeItem('token');
      localStorage.removeItem('user_id');
      localStorage.removeItem('user_role');
      return {};
    }
    
    return decoded;
  } catch (_) {
    return {};
  }
}

export function getAuthState() {
  const token = localStorage.getItem('token') || '';
  const claims = token ? decodeJwtPayload(token) : {};
  const userData = claims.data || {};

  return {
    token,
    userId: userData.id || '',
    email: userData.email || '',
    nome: userData.nome || 'Usuário',
    nome_fantasia: userData.nome_fantasia || null,
    role: (userData.role || '').toLowerCase(),
    companyId: Number(userData.empresa_id || 0)
  };
}

export function isAuthenticated() {
  return Boolean(getAuthState().token);
}

export function clearAuth() {
  localStorage.removeItem('token');
  localStorage.removeItem('user_id');
  localStorage.removeItem('user_role');
}

export function redirectToLogin(returnTo = window.location.pathname) {
  const loginUrl = new URL('./login.html', window.location.href);
  loginUrl.searchParams.set('redirect', returnTo);
  window.location.href = loginUrl.toString();
}

export function requireAuth({ role } = {}) {
  const auth = getAuthState();

  if (!auth.token) {
    console.warn("[AUTH] Token não encontrado — redirecionando para login");
    window.location.href = new URL('./login.html', window.location.href).toString();
    return null;
  }

  if (role && auth.role !== role) {
    const fallback = auth.role === 'empresa' ? './dashboard.html' : './candidate-dashboard.html';
    window.location.href = new URL(fallback, window.location.href).toString();
    return null;
  }

  return auth;
}

export function redirectIfAuthenticated() {
  const auth = getAuthState();
  if (!auth.token) return;

  const path = window.location.pathname.toLowerCase();
  const isPublicPage = path.endsWith('/') || 
                       path.includes('index.html') ||
                       path.includes('home.html') ||
                       path.includes('job.html') ||
                       path.includes('vaga.html') ||
                       path.includes('explorar.html');

  if (isPublicPage) {
    console.log("[AUTH] Public page detected, skipping auto-redirect: ", path);
    return;
  }

  const params = new URLSearchParams(window.location.search);
  const redirect = params.get('redirect');

  if (redirect) {
    window.location.href = redirect;
    return;
  }

  const target = auth.role === 'empresa' ? './dashboard.html' : './candidate-dashboard.html';
  window.location.href = new URL(target, window.location.href).toString();
}

export async function logoutAndRedirect() {
  try {
    if (isAuthenticated()) {
      await logoutRequest();
    }
  } catch (_) {
    // fallback para logout local quando token já expirou
  } finally {
    clearAuth();
    window.location.href = new URL('./login.html', window.location.href).toString();
  }
}
