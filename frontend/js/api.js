import { API_URL } from './config.js';

export class ApiError extends Error {
  constructor(message, status = 0, payload = null) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.payload = payload;
  }
}

export function getToken() {
  return localStorage.getItem('token') || '';
}

export function normalizeApiResponse(response) {
  // Estrutura base para falhas: um array vazio com propriedade pagination
  const createEmpty = () => {
    const arr = [];
    arr.pagination = { total: 0, page: 1, total_pages: 0 };
    arr.vagas = arr;
    arr.items = arr;
    return arr;
  };

  if (!response) return createEmpty();
  
  // Se for sucesso mas sem data (ex: DELETE), retorna o próprio response
  if (response.success && response.data === undefined) return response;

  const data = response.data !== undefined ? response.data : response;
  if (!data) return createEmpty();

  let result = [];
  let pagination = { total: 0, page: 1, total_pages: 0 };

  if (typeof data === 'object' && Array.isArray(data.items)) {
    // Formato com paginação { items: [], meta: {} } ou { items: [], total: 0 }
    result = data.items;
    pagination = data.meta || {
      total: data.total || data.items.length,
      page: data.page || 1,
      total_pages: data.total_pages || 1
    };
  } else if (Array.isArray(data)) {
    // Formato array simples
    result = data;
    pagination = { total: data.length, page: 1, total_pages: 1 };
  } else if (data && typeof data === 'object') {
    // Caso já seja o objeto formatado ou outro objeto
    if (Array.isArray(data.vagas)) return data; 
    return data;
  }

  // A MÁGICA: Retornamos o array mas anexamos as propriedades que o jobs.js e outros esperam
  result.pagination = pagination;
  result.vagas = result;
  result.items = result;
  
  return result;
}

export function getAuthHeaders() {
  const token = localStorage.getItem('token');
  console.log('[AUTH TOKEN]', token);

  return {
    ...(token ? { Authorization: `Bearer ${token}` } : {})
  };
}

async function request(path, options = {}) {
  const url = `${API_URL}${path}`;
  console.log('[API] CALL:', url);

  const isFormData = options.body instanceof FormData;
  const headers = {
    ...getAuthHeaders(),
    ...(isFormData ? {} : { 'Content-Type': 'application/json' }),
    ...(options.headers || {})
  };

  const response = await fetch(url, {
    ...options,
    headers
  }).catch(err => {
    console.error("[FETCH ERROR]", err);
    throw err;
  });

  const contentType = response.headers.get('content-type') || '';
  const payload = contentType.includes('application/json') ? await response.json() : null;

  if (payload && !payload.success) {
    console.error('[API ERROR]', payload?.message);
  }

  if (!response.ok) {
    const message = payload?.message || `Erro HTTP ${response.status}`;

    if (response.status === 401 && localStorage.getItem('token') && !window.location.pathname.includes('login')) {
      console.warn('[AUTH] Sessão expirada ou inválida. Limpando credenciais.');
      localStorage.removeItem('token');
      localStorage.removeItem('user_id');
      localStorage.removeItem('user_role');
    }

    throw new ApiError(message, response.status, payload);
  }

  return payload;
}

export async function login(credentials) {
  return request('/auth/login', {
    method: 'POST',
    body: JSON.stringify(credentials)
  });
}

export async function logout() {
  return request('/auth/logout', { method: 'POST' });
}

export async function register(payload) {
  return request('/auth/register', {
    method: 'POST',
    body: JSON.stringify(payload)
  });
}

export async function getJobs(query = '') {
  return request(`/jobs${query}`);
}

export async function getJobById(id) {
  return request(`/jobs/${id}`);
}

export async function applyToJob(payload) {
  return request('/applications', {
    method: 'POST',
    body: (payload instanceof FormData) ? payload : JSON.stringify(payload)
  });
}

export async function getMyApplications(page = 1, limit = 10) {
  return request(`/applications/me?page=${page}&limit=${limit}`);
}

export async function getJobApplications(jobId, page = 1, limit = 10) {
  return request(`/jobs/${jobId}/applications?page=${page}&limit=${limit}`);
}

export async function getApplicationById(id) {
  return request(`/applications/${id}`);
}

export async function createJob(payload) {
  return request('/jobs', {
    method: 'POST',
    body: JSON.stringify(payload)
  });
}

export async function updateApplicationStatus(applicationId, status) {
  return request(`/applications/${applicationId}`, {
    method: 'PUT',
    body: JSON.stringify({ status })
  });
}

export async function saveFavorite(jobId) {
  return request('/favorites', {
    method: 'POST',
    body: JSON.stringify({ job_id: jobId })
  });
}

export async function getFavorites() {
  return request('/favorites');
}

export async function removeFavorite(favoriteId) {
  return request(`/favorites/${favoriteId}`, {
    method: 'DELETE'
  });
}

export async function updateJob(id, payload) {
  return request(`/jobs/${id}`, {
    method: 'PUT',
    body: JSON.stringify(payload)
  });
}

export async function toggleJobStatus(id) {
  return request(`/jobs/${id}/status`, {
    method: 'PUT'
  });
}

export async function concludeJob(id) {
  return request(`/jobs/${id}/conclude`, {
    method: 'PUT'
  });
}

export async function deleteJob(id) {
  return request(`/jobs/${id}`, {
    method: 'DELETE'
  });
}

// Generic REST Helpers
export const get = (path) => request(path, { method: 'GET' });
export const post = (path, body) => request(path, { method: 'POST', body: (body instanceof FormData) ? body : JSON.stringify(body) });
export const put = (path, body) => request(path, { method: 'PUT', body: (body instanceof FormData) ? body : JSON.stringify(body) });
export const del = (path) => request(path, { method: 'DELETE' });

export { API_URL };

const API = { get, post, put, delete: del, API_URL };
export default API;
