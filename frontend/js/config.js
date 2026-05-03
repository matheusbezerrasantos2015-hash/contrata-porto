/**
 * Configuração centralizada da API — ContrataPorto
 * Detecta dinamicamente o projectRoot via pathname para funcionar
 * em qualquer subpasta do XAMPP sem hardcode.
 */

// Limpa qualquer URL antiga que possa estar no localStorage (legado PortoFerreiraJobs)
['api_base_url', 'api_url'].forEach(key => {
  const val = localStorage.getItem(key);
  if (val && !val.includes('ContrataPorto')) {
    console.warn('[CONFIG] Removendo URL legada do localStorage:', val);
    localStorage.removeItem(key);
  }
});

const origin = window.location.origin;
const pathParts = window.location.pathname.split('/');
const projectRoot = `/${pathParts[1]}`; // ex: /ContrataPorto

export const API_URL = `${origin}${projectRoot}/backend/public/index.php/api`;
export const API_BASE = `${origin}${projectRoot}/backend/public/index.php`;

console.log('[CONFIG] API_URL:', API_URL);
