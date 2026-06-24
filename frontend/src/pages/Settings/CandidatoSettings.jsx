import { useForm } from 'react-hook-form'
import { useAuth } from '@/contexts/AuthContext'
import { useState, useEffect, useRef } from 'react'
import * as userApi from '@/api/user'
import { User, Phone, MapPin, AlertCircle, CheckCircle2, Trash2, Camera } from 'lucide-react'
import Input from '@/components/Input'
import Select from '@/components/Select'
import Button from '@/components/Button'
import Modal from '@/components/Modal'
import useDocumentTitle from '@/hooks/useDocumentTitle'

const ESTADOS = [
  { value: 'SP', label: 'São Paulo' },
  { value: 'RS', label: 'Rio Grande do Sul' },
  { value: 'SC', label: 'Santa Catarina' },
  { value: 'PR', label: 'Paraná' },
  { value: 'RJ', label: 'Rio de Janeiro' },
]

export default function CandidatoSettings() {
  useDocumentTitle('Configurações do Candidato')
  const { user, updateUser, updateToken, logout } = useAuth()
  const { register, handleSubmit, formState: { errors }, reset } = useForm()

  const [loading, setLoading] = useState(false)
  const [successMsg, setSuccessMsg] = useState('')
  const [errorMsg, setErrorMsg] = useState('')

  // Avatar
  const [avatarPreview, setAvatarPreview] = useState(null)
  const [avatarFile, setAvatarFile] = useState(null)
  const avatarInputRef = useRef(null)

  // Exclusão de conta
  const [deleteModalOpen, setDeleteModalOpen] = useState(false)
  const [deleteLoading, setDeleteLoading] = useState(false)
  const [deleteError, setDeleteError] = useState('')

  useEffect(() => {
    if (user) {
      reset({
        nome:     user.nome ?? '',
        telefone: user.telefone ?? '',
        cidade:   user.cidade ?? '',
        estado:   user.estado ?? '',
      })
    }
  }, [user, reset])

  // Quando o usuário seleciona um arquivo de avatar
  const handleAvatarChange = (e) => {
    const file = e.target.files?.[0]
    if (!file) return

    // Validação básica no frontend
    const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
    if (!allowed.includes(file.type)) {
      setErrorMsg('Apenas imagens JPEG, PNG ou WebP são aceitas.')
      return
    }
    if (file.size > 2 * 1024 * 1024) {
      setErrorMsg('O avatar deve ter no máximo 2 MB.')
      return
    }

    setErrorMsg('')
    setAvatarFile(file)
    setAvatarPreview(URL.createObjectURL(file))
  }

  const onSubmit = async (data) => {
    setLoading(true)
    setErrorMsg('')
    setSuccessMsg('')

    try {
      // Monta FormData para suportar upload de arquivo
      const formData = new FormData()
      formData.append('nome',     data.nome)
      formData.append('telefone', data.telefone ?? '')
      formData.append('cidade',   data.cidade ?? '')
      formData.append('estado',   data.estado ?? '')
      if (avatarFile) {
        formData.append('avatar', avatarFile)
      }

      const res = await userApi.updateProfile(formData)
      const resData = res.data?.data ?? {}

      // Atualiza contexto e token
      updateUser({
        nome:   resData.nome ?? data.nome,
        avatar: resData.avatar ?? user?.avatar,
      })
      if (resData.token) updateToken(resData.token)

      setAvatarFile(null)
      setSuccessMsg('Configurações salvas com sucesso!')
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Ocorreu um erro ao atualizar as configurações.')
    } finally {
      setLoading(false)
    }
  }

  const handleDeleteAccount = async () => {
    setDeleteLoading(true)
    setDeleteError('')
    try {
      await userApi.deleteAccount()
      setDeleteModalOpen(false)
      await logout()
    } catch (err) {
      setDeleteError(err.response?.data?.message ?? 'Não foi possível excluir a conta.')
    } finally {
      setDeleteLoading(false)
    }
  }

  // Foto exibida: preview local > avatar salvo no servidor > placeholder
  const displayAvatar = avatarPreview ?? user?.avatar ?? null

  return (
    <div className="page-wrapper py-10 max-w-3xl">
      <div className="mb-8">
        <h1 className="text-2xl sm:text-3xl font-bold text-primary-600 font-sans">Minhas Configurações</h1>
        <p className="text-slate-500 text-sm mt-1">Gerencie suas informações pessoais e dados cadastrais.</p>
      </div>

      <div className="card p-6 sm:p-8 bg-white space-y-6 border border-surface-200 shadow-card">
        {successMsg && (
          <div className="p-4 bg-success-50 border border-success-500/30 rounded-xl text-success-700 text-sm flex items-start gap-2.5">
            <CheckCircle2 className="w-5 h-5 flex-shrink-0 mt-0.5 text-success-500" />
            <span>{successMsg}</span>
          </div>
        )}

        {errorMsg && (
          <div className="p-4 bg-danger-50 border border-danger-500/30 rounded-xl text-danger-700 text-sm flex items-start gap-2.5">
            <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5 text-danger-500" />
            <span>{errorMsg}</span>
          </div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">

          {/* ── Avatar ─────────────────────────────────────────────────────── */}
          <div className="flex items-center gap-5">
            <div className="relative flex-shrink-0">
              {displayAvatar ? (
                <img
                  src={displayAvatar}
                  alt="Avatar"
                  className="w-20 h-20 rounded-full object-cover border-2 border-primary-200 shadow"
                />
              ) : (
                <div className="w-20 h-20 rounded-full bg-primary-100 border-2 border-primary-200 flex items-center justify-center shadow">
                  <User className="w-8 h-8 text-primary-400" />
                </div>
              )}
              <button
                type="button"
                onClick={() => avatarInputRef.current?.click()}
                className="absolute -bottom-1 -right-1 bg-primary-600 hover:bg-primary-700 text-white rounded-full p-1.5 shadow transition-colors"
                title="Alterar foto"
              >
                <Camera className="w-3.5 h-3.5" />
              </button>
            </div>
            <div>
              <p className="text-sm font-medium text-slate-700">Foto de perfil</p>
              <p className="text-xs text-slate-400 mt-0.5">JPEG, PNG ou WebP · máx 2 MB</p>
              {avatarFile && (
                <p className="text-xs text-primary-600 mt-1 font-medium">
                  {avatarFile.name} selecionado
                </p>
              )}
            </div>
            <input
              ref={avatarInputRef}
              type="file"
              accept="image/jpeg,image/jpg,image/png,image/webp"
              className="hidden"
              onChange={handleAvatarChange}
            />
          </div>

          {/* ── Campos do formulário ────────────────────────────────────────── */}
          <Input
            label="E-mail de Login"
            type="email"
            value={user?.email ?? ''}
            disabled
            hint="O e-mail não pode ser editado."
          />

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Input
              label="Nome Completo"
              type="text"
              placeholder="Seu nome"
              required
              leftIcon={<User className="w-4 h-4" />}
              error={errors.nome?.message}
              {...register('nome', { required: 'Nome é obrigatório' })}
            />

            <Input
              label="Telefone"
              type="tel"
              placeholder="(51) 99999-9999"
              leftIcon={<Phone className="w-4 h-4" />}
              error={errors.telefone?.message}
              {...register('telefone')}
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Input
              label="Cidade"
              type="text"
              placeholder="Porto Ferreira, SP"
              leftIcon={<MapPin className="w-4 h-4" />}
              error={errors.cidade?.message}
              {...register('cidade')}
            />

            <Select
              label="Estado"
              options={ESTADOS}
              {...register('estado')}
            />
          </div>

          <div className="pt-2">
            <Button type="submit" variant="primary" loading={loading} className="rounded-xl">
              Salvar Alterações
            </Button>
          </div>
        </form>

        <div className="border-t border-surface-200 pt-6 mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h3 className="text-sm font-bold text-danger-700">Deletar Conta</h3>
            <p className="text-xs text-slate-500 mt-1">Excluir sua conta removerá todos os seus dados e currículos salvos permanentemente.</p>
          </div>
          <Button
            onClick={() => setDeleteModalOpen(true)}
            variant="danger"
            className="rounded-xl text-xs flex-shrink-0"
            leftIcon={<Trash2 className="w-4 h-4" />}
          >
            Excluir minha conta
          </Button>
        </div>
      </div>

      {/* Modal Confirmação */}
      <Modal isOpen={deleteModalOpen} onClose={() => setDeleteModalOpen(false)} title="Excluir minha conta">
        <div className="space-y-4">
          {deleteError && (
            <div className="p-3 bg-danger-50 border border-danger-500/30 rounded-xl text-danger-700 text-xs flex items-start gap-2">
              <AlertCircle className="w-4 h-4 flex-shrink-0 mt-0.5" />
              <span>{deleteError}</span>
            </div>
          )}

          <p className="text-sm text-slate-600 leading-relaxed">
            Tem certeza que deseja prosseguir? Esta ação não pode ser desfeita e todas as suas candidaturas serão permanentemente apagadas.
          </p>

          <div className="flex gap-3 pt-2">
            <Button type="button" variant="outline" fullWidth onClick={() => setDeleteModalOpen(false)}>
              Cancelar
            </Button>
            <Button type="button" variant="danger" fullWidth loading={deleteLoading} onClick={handleDeleteAccount}>
              Confirmar Exclusão
            </Button>
          </div>
        </div>
      </Modal>
    </div>
  )
}
