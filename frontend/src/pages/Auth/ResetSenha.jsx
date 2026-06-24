import { useForm } from 'react-hook-form'
import { useNavigate, useSearchParams } from 'react-router-dom'
import * as authApi from '@/api/auth'
import { useState } from 'react'
import { Lock, CheckCircle2, AlertCircle } from 'lucide-react'
import Input from '@/components/Input'
import Button from '@/components/Button'
import useDocumentTitle from '@/hooks/useDocumentTitle'

export default function ResetSenha() {
  useDocumentTitle('Redefinir Senha')
  const [searchParams] = useSearchParams()
  const token = searchParams.get('token') ?? ''
  const { register, handleSubmit, watch, formState: { errors } } = useForm()

  const [loading, setLoading] = useState(false)
  const [errorMsg, setErrorMsg] = useState('')
  const [successMsg, setSuccessMsg] = useState('')
  const navigate = useNavigate()

  const novaSenhaVal = watch('nova_senha')

  const onSubmit = async (data) => {
    if (!token) {
      setErrorMsg('Token de redefinição ausente ou inválido.')
      return
    }
    setLoading(true)
    setErrorMsg('')
    setSuccessMsg('')
    try {
      await authApi.resetPassword(token, data.nova_senha)
      setSuccessMsg('Senha alterada com sucesso! Redirecionando para a tela de login...')
      setTimeout(() => {
        navigate('/login')
      }, 3000)
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Erro ao redefinir a senha. O link pode ter expirado.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full bg-white rounded-2xl shadow-card border border-surface-200 p-6 sm:p-8">
        <div className="text-center mb-8">
          <div className="w-12 h-12 bg-primary-50 text-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <Lock className="w-6 h-6" />
          </div>
          <h2 className="text-2xl sm:text-3xl font-bold text-primary-600 font-sans">Defina a nova senha</h2>
          <p className="text-slate-500 text-sm mt-2">
            Escolha uma nova senha forte de acesso para a sua conta.
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
            label="Nova Senha"
            type="password"
            placeholder="No mínimo 6 caracteres"
            required
            error={errors.nova_senha?.message}
            {...register('nova_senha', {
              required: 'A nova senha é obrigatória',
              minLength: { value: 6, message: 'A senha deve conter no mínimo 6 caracteres' },
            })}
          />

          <Input
            label="Confirmar Nova Senha"
            type="password"
            placeholder="Confirme a nova senha"
            required
            error={errors.confirmar_senha?.message}
            {...register('confirmar_senha', {
              required: 'A confirmação é obrigatória',
              validate: (val) => val === novaSenhaVal || 'As senhas não coincidem',
            })}
          />

          <Button type="submit" variant="primary" fullWidth loading={loading} className="mt-2">
            Alterar Senha
          </Button>
        </form>
      </div>
    </div>
  )
}
