import { useForm } from 'react-hook-form'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { useAuth } from '@/contexts/AuthContext'
import { useState, useEffect } from 'react'
import { Mail, Lock, AlertCircle } from 'lucide-react'
import Input from '@/components/Input'
import Button from '@/components/Button'

export default function Login() {
  const { login, isAuthenticated, userType } = useAuth()
  const { register, handleSubmit, formState: { errors } } = useForm()
  const [loading, setLoading] = useState(false)
  const [errorMsg, setErrorMsg] = useState('')
  const navigate = useNavigate()
  const location = useLocation()

  useEffect(() => {
    if (isAuthenticated) {
      const from = location.state?.from?.pathname
      if (from) {
        navigate(from, { replace: true })
      } else {
        navigate(userType === 'EMPRESA' ? '/dashboard/empresa' : '/dashboard/candidato', { replace: true })
      }
    }
  }, [isAuthenticated, userType, navigate, location])

  const onSubmit = async (data) => {
    setLoading(true)
    setErrorMsg('')
    try {
      const { user } = await login(data.email, data.senha)
      const from = location.state?.from?.pathname
      if (from) {
        navigate(from, { replace: true })
      } else {
        navigate(user.role === 'EMPRESA' ? '/dashboard/empresa' : '/dashboard/candidato', { replace: true })
      }
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Credenciais inválidas. Tente novamente.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full bg-white rounded-2xl shadow-card border border-surface-200 p-8">
        <div className="text-center mb-8">
          <h2 className="text-3xl font-bold text-primary-600 font-sans">Acesse sua conta</h2>
          <p className="text-slate-500 text-sm mt-2">
            Insira suas credenciais para continuar no ContrataPorto.
          </p>
        </div>

        {errorMsg && (
          <div className="mb-6 p-4 bg-danger-50 border border-danger-500/30 rounded-xl text-danger-700 text-sm flex items-start gap-2.5">
            <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5 text-danger-500" />
            <span>{errorMsg}</span>
          </div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
          <Input
            label="E-mail"
            type="email"
            placeholder="seuemail@exemplo.com"
            required
            leftIcon={<Mail className="w-4 h-4" />}
            error={errors.email?.message}
            {...register('email', {
              required: 'O e-mail é obrigatório',
              pattern: {
                value: /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i,
                message: 'E-mail inválido',
              },
            })}
          />

          <div>
            <div className="flex justify-between items-center mb-1.5">
              <label className="text-sm font-medium text-slate-700">Senha</label>
              <Link to="/esqueci-senha" className="text-xs text-primary-600 hover:text-primary-700 no-underline font-semibold">
                Esqueceu a senha?
              </Link>
            </div>
            <Input
              type="password"
              placeholder="Sua senha secreta"
              required
              leftIcon={<Lock className="w-4 h-4" />}
              error={errors.senha?.message}
              {...register('senha', {
                required: 'A senha é obrigatória',
                minLength: { value: 6, message: 'A senha deve conter no mínimo 6 caracteres' },
              })}
            />
          </div>

          <Button type="submit" variant="primary" fullWidth loading={loading} className="mt-2">
            Entrar
          </Button>
        </form>

        <p className="text-center text-sm text-slate-500 mt-6">
          Não tem uma conta?{' '}
          <Link to="/cadastro" className="font-semibold text-primary-600 hover:text-primary-700 no-underline">
            Cadastre-se gratuitamente
          </Link>
        </p>
      </div>
    </div>
  )
}
