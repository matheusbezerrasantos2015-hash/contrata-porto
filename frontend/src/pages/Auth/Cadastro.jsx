import { useForm } from 'react-hook-form'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import * as authApi from '@/api/auth'
import { useState } from 'react'
import { User, Mail, Lock, Building2, FileText, Phone, AlertCircle } from 'lucide-react'
import Input from '@/components/Input'
import Button from '@/components/Button'
import useDocumentTitle from '@/hooks/useDocumentTitle'

export default function Cadastro() {
  useDocumentTitle('Cadastro')
  const [searchParams] = useSearchParams()
  const [role, setRole] = useState(() => {
    const queryRole = searchParams.get('role')
    return queryRole === 'EMPRESA' ? 'EMPRESA' : 'CANDIDATO'
  })
  const { register, handleSubmit, watch, formState: { errors } } = useForm()
  const [loading, setLoading] = useState(false)
  const [errorMsg, setErrorMsg] = useState('')
  const navigate = useNavigate()

  const senhaVal = watch('senha')

  const onSubmit = async (data) => {
    setLoading(true)
    setErrorMsg('')
    try {
      const payload = {
        nome: data.nome,
        email: data.email,
        senha: data.senha,
        role: role,
        ...(role === 'EMPRESA' && {
          company: {
            nome_fantasia: data.nome_fantasia,
            cnpj: data.cnpj.replace(/\D/g, ''),
            telefone: data.telefone_empresa,
          },
        }),
      }

      await authApi.register(payload)
      navigate(`/verificar-email?email=${encodeURIComponent(data.email)}`)
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Ocorreu um erro ao cadastrar. Verifique os dados.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-[85vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full bg-white rounded-2xl shadow-card border border-surface-200 p-6 sm:p-8">
        <div className="text-center mb-6">
          <h2 className="text-2xl sm:text-3xl font-bold text-primary-600 font-sans">Crie sua conta</h2>
          <p className="text-slate-500 text-sm mt-2">
            Escolha o tipo de conta e preencha os dados abaixo.
          </p>
        </div>

        {/* Seleção de Role */}
        <div className="flex bg-surface-100 p-1 rounded-xl mb-6">
          <button
            type="button"
            className={`flex-1 py-2.5 text-sm font-semibold rounded-lg transition-all ${
              role === 'CANDIDATO'
                ? 'bg-white text-primary-600 shadow-sm'
                : 'text-slate-500 hover:text-slate-800'
            }`}
            onClick={() => setRole('CANDIDATO')}
          >
            Sou Candidato
          </button>
          <button
            type="button"
            className={`flex-1 py-2.5 text-sm font-semibold rounded-lg transition-all ${
              role === 'EMPRESA'
                ? 'bg-white text-primary-600 shadow-sm'
                : 'text-slate-500 hover:text-slate-800'
            }`}
            onClick={() => setRole('EMPRESA')}
          >
            Sou Empresa
          </button>
        </div>

        {errorMsg && (
          <div className="mb-5 p-4 bg-danger-50 border border-danger-500/30 rounded-xl text-danger-700 text-sm flex items-start gap-2.5">
            <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5 text-danger-500" />
            <span>{errorMsg}</span>
          </div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <Input
            label={role === 'EMPRESA' ? 'Nome do Responsável' : 'Nome Completo'}
            type="text"
            placeholder="Ex: João da Silva"
            required
            leftIcon={<User className="w-4 h-4" />}
            error={errors.nome?.message}
            {...register('nome', { required: 'O nome é obrigatório' })}
          />

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

          {role === 'EMPRESA' && (
            <>
              <div className="border-t border-surface-200 pt-4 mt-2">
                <span className="text-xs font-bold text-slate-400 tracking-wider uppercase block mb-3">
                  Dados da Empresa
                </span>
              </div>

              <Input
                label="Nome Fantasia"
                type="text"
                placeholder="Nome da sua empresa"
                required
                leftIcon={<Building2 className="w-4 h-4" />}
                error={errors.nome_fantasia?.message}
                {...register('nome_fantasia', { required: 'O nome fantasia é obrigatório' })}
              />

              <Input
                label="CNPJ"
                type="text"
                placeholder="00.000.000/0000-00"
                required
                leftIcon={<FileText className="w-4 h-4" />}
                error={errors.cnpj?.message}
                {...register('cnpj', {
                  required: 'O CNPJ é obrigatório',
                  pattern: {
                    value: /^\d{2}\.?\d{3}\.?\d{3}\/?\d{4}-?\d{2}$/,
                    message: 'Formato de CNPJ inválido',
                  },
                })}
              />

              <Input
                label="Telefone da Empresa"
                type="tel"
                placeholder="(51) 99999-9999"
                leftIcon={<Phone className="w-4 h-4" />}
                error={errors.telefone_empresa?.message}
                {...register('telefone_empresa')}
              />
            </>
          )}

          <div className="border-t border-surface-200 pt-4 mt-2">
            <span className="text-xs font-bold text-slate-400 tracking-wider uppercase block mb-3">
              Senha de Acesso
            </span>
          </div>

          <Input
            label="Senha"
            type="password"
            placeholder="Mínimo 6 caracteres"
            required
            leftIcon={<Lock className="w-4 h-4" />}
            error={errors.senha?.message}
            {...register('senha', {
              required: 'A senha é obrigatória',
              minLength: { value: 6, message: 'A senha deve conter no mínimo 6 caracteres' },
            })}
          />

          <Input
            label="Confirmar Senha"
            type="password"
            placeholder="Confirme a senha"
            required
            leftIcon={<Lock className="w-4 h-4" />}
            error={errors.confirmar_senha?.message}
            {...register('confirmar_senha', {
              required: 'A confirmação de senha é obrigatória',
              validate: (val) => val === senhaVal || 'As senhas não coincidem',
            })}
          />

          <Button type="submit" variant="primary" fullWidth loading={loading} className="mt-4">
            Criar Conta
          </Button>
        </form>

        <p className="text-center text-sm text-slate-500 mt-6 font-sans">
          Já tem uma conta?{' '}
          <Link to="/login" className="font-semibold text-primary-600 hover:text-primary-700 no-underline">
            Entrar
          </Link>
        </p>
      </div>
    </div>
  )
}
