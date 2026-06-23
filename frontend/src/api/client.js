import axios from 'axios'

/**
 * Instância Axios configurada para a API do ContrataPorto.
 *
 * - baseURL: VITE_API_URL (padrão: /api — resolvido pelo proxy Vite em dev)
 * - Interceptor de request: injeta Authorization: Bearer <token> do localStorage
 * - Interceptor de response: limpa token e redireciona se 401
 */
const TOKEN_KEY = 'cp_token'

const client = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  withCredentials: false,
})

// ─── Request Interceptor ────────────────────────────────────────────────────
client.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// ─── Response Interceptor ───────────────────────────────────────────────────
client.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem(TOKEN_KEY)
      // Evita redirect em rotas públicas (login/cadastro)
      const publicPaths = ['/login', '/cadastro', '/esqueci-senha', '/reset-senha', '/verificar-email']
      const isPublic = publicPaths.some((p) => window.location.pathname.startsWith(p))
      if (!isPublic) {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export { TOKEN_KEY }
export default client
