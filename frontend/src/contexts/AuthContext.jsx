import { createContext, useCallback, useContext, useEffect, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import * as authApi from '@/api/auth'
import * as userApi from '@/api/user'
import { TOKEN_KEY } from '@/api/client'

/**
 * AuthContext — Contexto de autenticação global do ContrataPorto
 *
 * Fornece:
 *  - user: Object | null
 *  - token: string | null
 *  - loading: boolean — true enquanto está verificando o token inicial
 *  - isAuthenticated: boolean
 *  - userType: 'CANDIDATO' | 'EMPRESA' | null
 *  - login(email, senha): Promise — loga e salva o token
 *  - logout(): Promise — faz logout na API e limpa o estado
 *  - updateUser(data): atualiza os dados do usuário no contexto sem nova requisição
 */

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [token, setToken] = useState(() => localStorage.getItem(TOKEN_KEY))
  const [loading, setLoading] = useState(true)
  const navigate = useNavigate()
  const initialized = useRef(false)

  // ─── Hidratar usuário ao carregar a aplicação ──────────────────────────────
  useEffect(() => {
    if (initialized.current) return
    initialized.current = true

    const savedToken = localStorage.getItem(TOKEN_KEY)
    if (!savedToken) {
      setLoading(false)
      return
    }

    userApi
      .me()
      .then((res) => {
        setUser(res.data?.data ?? null)
      })
      .catch(() => {
        // Token inválido ou expirado — limpa o estado
        localStorage.removeItem(TOKEN_KEY)
        setToken(null)
        setUser(null)
      })
      .finally(() => setLoading(false))
  }, [])

  // ─── Login ─────────────────────────────────────────────────────────────────
  const login = useCallback(async (email, senha) => {
    const res = await authApi.login(email, senha)
    const { data } = res.data

    const authToken = data?.auth?.token
    const userData  = data?.usuario

    if (!authToken) throw new Error('Token não recebido.')

    localStorage.setItem(TOKEN_KEY, authToken)
    setToken(authToken)
    setUser(userData)

    return { user: userData, token: authToken }
  }, [])

  // ─── Logout ────────────────────────────────────────────────────────────────
  const logout = useCallback(async () => {
    try {
      await authApi.logout()
    } catch (_) {
      // Ignora erros de rede — o cliente já pode estar desconectado
    } finally {
      localStorage.removeItem(TOKEN_KEY)
      setToken(null)
      setUser(null)
      navigate('/login', { replace: true })
    }
  }, [navigate])

  // ─── Atualização local do usuário ──────────────────────────────────────────
  const updateUser = useCallback((data) => {
    setUser((prev) => ({ ...prev, ...data }))
  }, [])

  // ─── Novo token (ex: após atualizar perfil) ────────────────────────────────
  const updateToken = useCallback((newToken) => {
    localStorage.setItem(TOKEN_KEY, newToken)
    setToken(newToken)
  }, [])

  const value = {
    user,
    token,
    loading,
    isAuthenticated: !!user && !!token,
    userType: user?.tipo ?? user?.role ?? null,
    login,
    logout,
    updateUser,
    updateToken,
  }

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

/**
 * Hook para consumir o AuthContext.
 * Deve ser usado dentro de <AuthProvider>.
 */
export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) {
    throw new Error('useAuth deve ser usado dentro de <AuthProvider>')
  }
  return ctx
}

export default AuthContext
