import { useForm } from 'react-hook-form'
import { Link } from 'react-router-dom'
import * as authApi from '@/api/auth'
import { useState } from 'react'
import { KeyRound, CheckCircle2, AlertCircle } from 'lucide-react'
import Input from '@/components/Input'
import Button from '@/components/Button'

export default function EsqueciSenha() {
  const { register, handleSubmit, formState: { errors } } = useForm()
  const [loading, setLoading] = useState(false)
  const [errorMsg, setErrorMsg] = useState('')
  const [successMsg, setSuccessMsg] = useState('')

  const onSubmit = async (data) => {
    setLoading(true)
    setErrorMsg('')
    setSuccessMsg('')
    try {
      await authApi.forgotPassword(data.email)
      setSuccessMsg('Se o e-mail estiver cadastrado, um link de recuperação foi enviado.')
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Erro ao solicitar recuperação. Tente novamente.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full bg-white rounded-2xl shadow-card border border-surface-200 p-8">
        <div className="text-center mb-8">
          <div className="w-12 h-12 bg-primary-50 text-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <KeyRound className="w-6 h-6" />
          </div>
          <h2 className="text-3xl font-bold text-primary-600 font-sans">Recupere sua senha</h2>
          <p className="text-slate-500 text-sm mt-2">
            Informe o seu e-mail cadastrado e enviaremos as instruções para você redefinir sua senha.
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
            label="E-mail de Cadastro"
            type="email"
            placeholder="seuemail@exemplo.com"
            required
            error={errors.email?.message}
            {...register('email', {
              required: 'O e-mail é obrigatório',
              pattern: {
                value: /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i,
                message: 'E-mail inválido',
              },
            })}
          />

          <Button type="submit" variant="primary" fullWidth loading={loading} className="mt-2">
            Enviar Instruções
          </Button>
        </form>

        <p className="text-center text-sm text-slate-500 mt-6">
          Lembrou a senha?{' '}
          <Link to="/login" className="font-semibold text-primary-600 hover:text-primary-700 no-underline">
            Voltar para o login
          </Link>
        </p>
      </div>
    </div>
  )
}
