import { useForm } from 'react-hook-form'
import { useNavigate, useSearchParams } from 'react-router-dom'
import * as authApi from '@/api/auth'
import { useState } from 'react'
import { Mail, CheckCircle2, AlertCircle } from 'lucide-react'
import Input from '@/components/Input'
import Button from '@/components/Button'
import useDocumentTitle from '@/hooks/useDocumentTitle'

export default function VerificarEmail() {
  useDocumentTitle('Verificar E-mail')
  const [searchParams] = useSearchParams()
  const email = searchParams.get('email') ?? ''
  const { register, handleSubmit, formState: { errors } } = useForm({
    defaultValues: { email },
  })

  const [loading, setLoading] = useState(false)
  const [resending, setResending] = useState(false)
  const [errorMsg, setErrorMsg] = useState('')
  const [successMsg, setSuccessMsg] = useState('')
  const navigate = useNavigate()

  const onSubmit = async (data) => {
    setLoading(true)
    setErrorMsg('')
    setSuccessMsg('')
    try {
      await authApi.verifyEmail(data.email, data.code)
      setSuccessMsg('E-mail verificado com sucesso! Redirecionando para o login...')
      setTimeout(() => {
        navigate('/login')
      }, 3000)
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Código de verificação incorreto ou expirado.')
    } finally {
      setLoading(false)
    }
  }

  const handleResend = async (data) => {
    if (!data.email) {
      setErrorMsg('Por favor, informe seu e-mail para reenviar o código.')
      return
    }
    setResending(true)
    setErrorMsg('')
    setSuccessMsg('')
    try {
      await authApi.resendVerification(data.email)
      setSuccessMsg('Um novo código de verificação foi enviado para o seu e-mail.')
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Erro ao reenviar o código. Tente novamente mais tarde.')
    } finally {
      setResending(false)
    }
  }

  return (
    <div className="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full bg-white rounded-2xl shadow-card border border-surface-200 p-6 sm:p-8">
        <div className="text-center mb-8">
          <div className="w-12 h-12 bg-primary-50 text-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <Mail className="w-6 h-6" />
          </div>
          <h2 className="text-2xl sm:text-3xl font-bold text-primary-600 font-sans">Verifique seu e-mail</h2>
          <p className="text-slate-500 text-sm mt-2">
            Insira o e-mail cadastrado e o código de 6 dígitos que enviamos para você.
          </p>
        </div>

        {errorMsg && (
          <div className="mb-6 p-4 bg-danger-50 border border-danger-500/30 rounded-xl text-danger-700 text-sm flex items-start gap-2.5">
            <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5 text-danger-500" />
            <span>{errorMsg}</span>
          </div>
        )}

        {successMsg && (
          <div className="mb-6 p-4 bg-success-50 border border-success-500/30 rounded-xl text-success-700 text-sm flex items-start gap-2.5">
            <CheckCircle2 className="w-5 h-5 flex-shrink-0 mt-0.5 text-success-500" />
            <span>{successMsg}</span>
          </div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
          <Input
            label="E-mail"
            type="email"
            placeholder="seuemail@exemplo.com"
            required
            error={errors.email?.message}
            {...register('email', { required: 'O e-mail é obrigatório' })}
          />

          <Input
            label="Código de Verificação"
            type="text"
            placeholder="Digite o código de 6 dígitos"
            maxLength={6}
            required
            error={errors.code?.message}
            {...register('code', {
              required: 'O código é obrigatório',
              pattern: { value: /^[0-9A-Z]{6}$/i, message: 'O código deve conter 6 caracteres alfanuméricos' },
            })}
          />

          <Button type="submit" variant="primary" fullWidth loading={loading} className="mt-2">
            Confirmar Código
          </Button>
        </form>

        <div className="mt-6 flex flex-col items-center justify-center gap-2">
          <p className="text-center text-sm text-slate-500">
            Não recebeu o código?
          </p>
          <button
            type="button"
            onClick={handleSubmit(handleResend)}
            disabled={resending}
            className="text-sm font-semibold text-primary-600 hover:text-primary-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {resending ? 'Reenviando...' : 'Reenviar código por e-mail'}
          </button>
        </div>
      </div>
    </div>
  )
}
