import { useForm } from 'react-hook-form'
import { useAuth } from '@/contexts/AuthContext'
import { useState, useEffect, useRef } from 'react'
import * as companyApi from '@/api/company'
import { Building2, Phone, Globe, AlertCircle, CheckCircle2, Trash2, FileText, ImagePlus } from 'lucide-react'
import Input from '@/components/Input'
import Button from '@/components/Button'
import Modal from '@/components/Modal'
import Spinner from '@/components/Spinner'

export default function EmpresaSettings() {
  const { updateToken, logout } = useAuth()
  const { register, handleSubmit, formState: { errors }, reset } = useForm()

  const [profileLoading, setProfileLoading] = useState(true)
  const [loading, setLoading] = useState(false)
  const [successMsg, setSuccessMsg] = useState('')
  const [errorMsg, setErrorMsg] = useState('')

  // Logo
  const [currentLogo, setCurrentLogo] = useState(null)
  const [logoPreview, setLogoPreview] = useState(null)
  const [logoFile, setLogoFile] = useState(null)
  const logoInputRef = useRef(null)

  // Exclusão de conta
  const [deleteModalOpen, setDeleteModalOpen] = useState(false)
  const [deleteLoading, setDeleteLoading] = useState(false)
  const [deleteError, setDeleteError] = useState('')

  useEffect(() => {
    setProfileLoading(true)
    companyApi
      .getProfile()
      .then((res) => {
        const comp = res.data?.data ?? {}
        reset({
          nome_fantasia: comp.nome_fantasia ?? '',
          cnpj:          comp.cnpj ?? '',
          telefone:      comp.telefone ?? '',
          site:          comp.site ?? '',
          descricao:     comp.descricao ?? '',
        })
        setCurrentLogo(comp.logo ?? null)
      })
      .catch(() => {
        setErrorMsg('Não foi possível carregar as configurações da empresa.')
      })
      .finally(() => setProfileLoading(false))
  }, [reset])

  // Quando o usuário seleciona um arquivo de logo
  const handleLogoChange = (e) => {
    const file = e.target.files?.[0]
    if (!file) return

    const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
    if (!allowed.includes(file.type)) {
      setErrorMsg('Apenas imagens JPEG, PNG ou WebP são aceitas.')
      return
    }
    if (file.size > 2 * 1024 * 1024) {
      setErrorMsg('O logo deve ter no máximo 2 MB.')
      return
    }

    setErrorMsg('')
    setLogoFile(file)
    setLogoPreview(URL.createObjectURL(file))
  }

  const onSubmit = async (data) => {
    setLoading(true)
    setErrorMsg('')
    setSuccessMsg('')
    try {
      // Monta FormData para suportar upload de arquivo
      const formData = new FormData()
      formData.append('nome_fantasia', data.nome_fantasia)
      formData.append('telefone',     data.telefone ?? '')
      formData.append('site',         data.site ?? '')
      formData.append('descricao',    data.descricao ?? '')
      if (logoFile) {
        formData.append('logo', logoFile)
      }

      const res = await companyApi.updateProfile(formData)
      const resData = res.data?.data ?? {}

      // Atualiza logo exibido e token
      if (resData.logo) setCurrentLogo(resData.logo)
      if (resData.token) updateToken(resData.token)

      setLogoFile(null)
      setLogoPreview(null)
      setSuccessMsg('Configurações da empresa salvas com sucesso!')
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Erro ao salvar configurações.')
    } finally {
      setLoading(false)
    }
  }

  const handleDeleteAccount = async () => {
    setDeleteLoading(true)
    setDeleteError('')
    try {
      await companyApi.deleteAccount()
      setDeleteModalOpen(false)
      await logout()
    } catch (err) {
      setDeleteError(err.response?.data?.message ?? 'Não foi possível excluir a conta corporativa.')
    } finally {
      setDeleteLoading(false)
    }
  }

  if (profileLoading) {
    return <Spinner size="lg" fullPage />
  }

  // Logo exibido: preview local > logo atual do servidor > placeholder
  const displayLogo = logoPreview ?? currentLogo ?? null

  return (
    <div className="page-wrapper py-10 max-w-3xl">
      <div className="mb-8">
        <h1 className="text-2xl sm:text-3xl font-bold text-primary-600 font-sans">Configurações da Empresa</h1>
        <p className="text-slate-500 text-sm mt-1">Atualize as informações públicas e dados cadastrais de sua organização.</p>
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

          {/* ── Logo da Empresa ─────────────────────────────────────────────── */}
          <div className="flex items-center gap-5">
            <div
              className="w-20 h-20 rounded-xl border-2 border-dashed border-primary-200 bg-primary-50 flex items-center justify-center overflow-hidden flex-shrink-0 cursor-pointer hover:border-primary-400 transition-colors"
              onClick={() => logoInputRef.current?.click()}
              title="Clique para alterar o logo"
            >
              {displayLogo ? (
                <img
                  src={displayLogo}
                  alt="Logo da empresa"
                  className="w-full h-full object-contain"
                />
              ) : (
                <ImagePlus className="w-8 h-8 text-primary-300" />
              )}
            </div>
            <div>
              <p className="text-sm font-medium text-slate-700">Logo da empresa</p>
              <p className="text-xs text-slate-400 mt-0.5">JPEG, PNG ou WebP · máx 2 MB</p>
              <button
                type="button"
                onClick={() => logoInputRef.current?.click()}
                className="mt-1.5 text-xs text-primary-600 hover:text-primary-700 font-medium underline underline-offset-2 transition-colors"
              >
                {displayLogo ? 'Alterar logo' : 'Adicionar logo'}
              </button>
              {logoFile && (
                <p className="text-xs text-primary-600 mt-0.5 font-medium">{logoFile.name} selecionado</p>
              )}
            </div>
            <input
              ref={logoInputRef}
              type="file"
              accept="image/jpeg,image/jpg,image/png,image/webp"
              className="hidden"
              onChange={handleLogoChange}
            />
          </div>

          {/* ── Campos do formulário ────────────────────────────────────────── */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Input
              label="Nome Fantasia"
              type="text"
              placeholder="Nome da sua empresa"
              required
              leftIcon={<Building2 className="w-4 h-4" />}
              error={errors.nome_fantasia?.message}
              {...register('nome_fantasia', { required: 'Nome Fantasia é obrigatório.' })}
            />

            <Input
              label="CNPJ"
              type="text"
              disabled
              leftIcon={<FileText className="w-4 h-4" />}
              hint="O CNPJ não pode ser alterado após o cadastro corporativo."
              {...register('cnpj')}
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Input
              label="Telefone de Contato"
              type="tel"
              placeholder="(51) 99999-9999"
              leftIcon={<Phone className="w-4 h-4" />}
              error={errors.telefone?.message}
              {...register('telefone')}
            />

            <Input
              label="Website Oficial"
              type="url"
              placeholder="https://suaempresa.com.br"
              leftIcon={<Globe className="w-4 h-4" />}
              error={errors.site?.message}
              {...register('site')}
            />
          </div>

          <div>
            <label className="label">Descrição da Empresa</label>
            <textarea
              placeholder="Fale um pouco sobre a área de atuação da organização..."
              className="input h-32 resize-none"
              {...register('descricao')}
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
            <h3 className="text-sm font-bold text-danger-700">Deletar Conta da Empresa</h3>
            <p className="text-xs text-slate-500 mt-1">Excluir a conta deletará todas as vagas e informações corporativas permanentemente.</p>
          </div>
          <Button
            onClick={() => setDeleteModalOpen(true)}
            variant="danger"
            className="rounded-xl text-xs flex-shrink-0"
            leftIcon={<Trash2 className="w-4 h-4" />}
          >
            Excluir empresa
          </Button>
        </div>
      </div>

      {/* Modal Deletar */}
      <Modal isOpen={deleteModalOpen} onClose={() => setDeleteModalOpen(false)} title="Deletar conta empresarial">
        <div className="space-y-4">
          {deleteError && (
            <div className="p-3 bg-danger-50 border border-danger-500/30 rounded-xl text-danger-700 text-xs flex items-start gap-2">
              <AlertCircle className="w-4 h-4 flex-shrink-0 mt-0.5" />
              <span>{deleteError}</span>
            </div>
          )}

          <p className="text-sm text-slate-600 leading-relaxed">
            Deseja prosseguir? Esta ação apagará de forma irreversível a sua conta de empresa e todas as vagas publicadas vinculadas a ela.
          </p>

          <div className="flex gap-3 pt-2">
            <Button type="button" variant="outline" fullWidth onClick={() => setDeleteModalOpen(false)}>
              Cancelar
            </Button>
            <Button type="button" variant="danger" fullWidth loading={deleteLoading} onClick={handleDeleteAccount}>
              Confirmar Deletar
            </Button>
          </div>
        </div>
      </Modal>
    </div>
  )
}
