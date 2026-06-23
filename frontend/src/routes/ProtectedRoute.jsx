import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { useAuth } from '@/contexts/AuthContext'
import { Loader2 } from 'lucide-react'

/**
 * ProtectedRoute — guarda de rotas que requerem autenticação.
 *
 * @prop {'CANDIDATO'|'EMPRESA'} role - se definido, também verifica o tipo de usuário
 * @prop {string} redirectTo - para onde redirecionar se não autenticado (padrão: /login)
 */
export default function ProtectedRoute({ role, redirectTo = '/login' }) {
  const { isAuthenticated, userType, loading } = useAuth()
  const location = useLocation()

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[60vh]">
        <Loader2 className="w-8 h-8 text-primary-500 animate-spin" />
      </div>
    )
  }

  if (!isAuthenticated) {
    return <Navigate to={redirectTo} state={{ from: location }} replace />
  }

  if (role && userType !== role) {
    // Redireciona para a home se logado mas com o tipo errado
    return <Navigate to="/" replace />
  }

  return <Outlet />
}
