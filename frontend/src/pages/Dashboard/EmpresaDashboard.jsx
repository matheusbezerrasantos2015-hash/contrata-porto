import { useEffect, useState, useCallback } from 'react'
import { Link } from 'react-router-dom'
import * as jobsApi from '@/api/jobs'
import { useForm } from 'react-hook-form'
import {
  Plus,
  Briefcase,
  Users,
  ChevronLeft,
  ChevronRight,
  AlertCircle,
  CheckCircle2,
  Power,
  Check,
} from 'lucide-react'
import Button from '@/components/Button'
import Modal from '@/components/Modal'
import Input from '@/components/Input'
import Select from '@/components/Select'
import Spinner from '@/components/Spinner'
import EmptyState from '@/components/EmptyState'
import {
  formatSalary,
  getStatusLabel,
  getStatusColor,
  formatDate,
} from '@/utils/formatters'
import useDocumentTitle from '@/hooks/useDocumentTitle'

const AREAS = [
  'Tecnologia da Informação',
  'Administração e Finanças',
  'Marketing e Vendas',
  'Recursos Humanos',
  'Atendimento e Suporte',
  'Design e Criação',
  'Logística e Operações',
  'Saúde e Bem-estar',
  'Educação',
  'Outros',
]

const NIVEIS = [
  { value: 'Estágio', label: 'Estágio' },
  { value: 'Júnior', label: 'Júnior' },
  { value: 'Pleno', label: 'Pleno' },
  { value: 'Sênior', label: 'Sênior' },
  { value: 'Especialista', label: 'Especialista' },
  { value: 'Gerência', label: 'Gerência' },
]

const TIPOS = [
  { value: 'CLT', label: 'CLT' },
  { value: 'PJ', label: 'PJ' },
  { value: 'Freelancer', label: 'Freelancer' },
  { value: 'Estágio', label: 'Estágio' },
  { value: 'Temporário', label: 'Temporário' },
  { value: 'Jovem_Aprendiz', label: 'Jovem Aprendiz' },
]

const MODALIDADES = [
  { value: 'presencial', label: 'Presencial' },
  { value: 'remoto', label: 'Remoto' },
  { value: 'hibrido', label: 'Híbrido' },
]

export default function EmpresaDashboard() {
  useDocumentTitle('Minhas Vagas')
  const [jobs, setJobs] = useState([])
  const [loading, setLoading] = useState(true)
  const [errorMsg, setErrorMsg] = useState('')
  const [successMsg, setSuccessMsg] = useState('')

  // Paginação
  const [page, setPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)

  // Modais de Criação/Edição
  const [formModalOpen, setFormModalOpen] = useState(false)
  const [editingJob, setEditingJob] = useState(null)
  const [formLoading, setFormLoading] = useState(false)
  const { register, handleSubmit, formState: { errors }, reset } = useForm()

  // Modal Deletar
  const [deleteModalOpen, setDeleteModalOpen] = useState(false)
  const [deletingJob, setDeletingJob] = useState(null)
  const [deleteLoading, setDeleteLoading] = useState(false)

  const fetchJobs = useCallback(async () => {
    setLoading(true)
    setErrorMsg('')
    try {
      const res = await jobsApi.myCompanyJobs(page)
      const { data, last_page } = res.data?.data ?? {}
      setJobs(data ?? [])
      setLastPage(last_page ?? 1)
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Erro ao buscar vagas da empresa.')
    } finally {
      setLoading(false)
    }
  }, [page])

  useEffect(() => {
    fetchJobs()
  }, [fetchJobs])

  const handleOpenCreate = () => {
    setEditingJob(null)
    reset({
      titulo: '',
      area: '',
      nivel: '',
      tipo: '',
      modalidade: '',
      cidade: '',
      estado: '',
      salario_min: '',
      salario_max: '',
      descricao: '',
      requisitos: '',
      diferenciais: '',
    })
    setFormModalOpen(true)
  }

  const handleOpenEdit = (job) => {
    setEditingJob(job)
    reset({
      titulo: job.titulo ?? '',
      area: job.area ?? '',
      nivel: job.nivel ?? '',
      tipo: job.tipo ?? '',
      modalidade: job.modalidade ?? '',
      cidade: job.cidade ?? '',
      estado: job.estado ?? '',
      salario_min: job.salario_min ?? '',
      salario_max: job.salario_max ?? '',
      descricao: job.descricao ?? '',
      requisitos: job.requisitos ?? '',
      diferenciais: job.diferenciais ?? '',
    })
    setFormModalOpen(true)
  }

  const handleFormSubmit = async (data) => {
    setFormLoading(true)
    setErrorMsg('')
    setSuccessMsg('')
    try {
      const payload = {
        ...data,
        salario_min: data.salario_min ? parseFloat(data.salario_min) : null,
        salario_max: data.salario_max ? parseFloat(data.salario_max) : null,
      }

      if (editingJob) {
        await jobsApi.update(editingJob.id, payload)
        setSuccessMsg('Vaga atualizada com sucesso!')
      } else {
        await jobsApi.create(payload)
        setSuccessMsg('Vaga publicada com sucesso!')
      }
      setFormModalOpen(false)
      fetchJobs()
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Erro ao salvar dados da vaga.')
    } finally {
      setFormLoading(false)
    }
  }

  const handleOpenDelete = (job) => {
    setDeletingJob(job)
    setDeleteModalOpen(true)
  }

  const handleDeleteSubmit = async () => {
    setDeleteLoading(true)
    setErrorMsg('')
    setSuccessMsg('')
    try {
      await jobsApi.remove(deletingJob.id)
      setSuccessMsg('Vaga removida com sucesso!')
      setDeleteModalOpen(false)
      fetchJobs()
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Erro ao remover vaga.')
    } finally {
      setDeleteLoading(false)
    }
  }

  const handleToggleStatus = async (jobId) => {
    setErrorMsg('')
    setSuccessMsg('')
    try {
      await jobsApi.toggleStatus(jobId)
      setSuccessMsg('Status da vaga alterado com sucesso!')
      fetchJobs()
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Erro ao alterar status da vaga.')
    }
  }

  const handleConclude = async (jobId) => {
    setErrorMsg('')
    setSuccessMsg('')
    try {
      await jobsApi.conclude(jobId)
      setSuccessMsg('Vaga concluída com sucesso! Ela ficará visível por mais 3 dias.')
      fetchJobs()
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Erro ao concluir vaga.')
    }
  }

  const handlePageChange = (newPage) => {
    if (newPage >= 1 && newPage <= lastPage) {
      setPage(newPage)
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }

  return (
    <div className="page-wrapper py-10">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 className="text-2xl sm:text-3xl font-bold text-primary-600 font-sans">Painel da Empresa</h1>
          <p className="text-slate-500 text-sm mt-1">Gerencie os anúncios e processos de seleção ativos.</p>
        </div>
        <Button
          onClick={handleOpenCreate}
          variant="primary"
          className="rounded-xl flex-shrink-0 animate-fade-in"
          leftIcon={<Plus className="w-5 h-5" />}
        >
          Publicar Nova Vaga
        </Button>
      </div>

      {successMsg && (
        <div className="mb-6 p-4 bg-success-50 border border-success-500/30 rounded-xl text-success-700 text-sm flex items-start gap-2.5">
          <CheckCircle2 className="w-5 h-5 flex-shrink-0 mt-0.5 text-success-500" />
          <span>{successMsg}</span>
        </div>
      )}

      {errorMsg && (
        <div className="mb-6 p-4 bg-danger-50 border border-danger-500/30 rounded-xl text-danger-700 text-sm flex items-start gap-2.5">
          <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5 text-danger-500" />
          <span>{errorMsg}</span>
        </div>
      )}

      {loading ? (
        <Spinner size="lg" />
      ) : jobs.length === 0 ? (
        <EmptyState
          icon={<Briefcase className="w-12 h-12 text-slate-300 animate-pulse" />}
          title="Sua empresa ainda não possui vagas"
          description="Comece agora mesmo a publicar anúncios de emprego para profissionais qualificados em Porto Ferreira, SP."
          action={
            <Button onClick={handleOpenCreate} variant="primary">
              Publicar Primeira Vaga
            </Button>
          }
        />
      ) : (
        <div className="space-y-4">
          {jobs.map((job) => (
            <article
              key={job.id}
              className="card p-6 bg-white flex flex-col md:flex-row md:items-center justify-between gap-6 border border-surface-200 shadow-card"
            >
              <div className="space-y-2 min-w-0 flex-1">
                <div className="flex items-center gap-2 flex-wrap">
                  <h3 className="text-base font-bold text-slate-800 leading-tight truncate">
                    {job.titulo}
                  </h3>
                  <span className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border ${getStatusColor(job.status)}`}>
                    {getStatusLabel(job.status)}
                  </span>
                </div>

                <div className="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                  <span>{job.area}</span>
                  <span>•</span>
                  <span>{formatSalary(job.salario_min, job.salario_max)}</span>
                  <span>•</span>
                  <span>Criada em {formatDate(job.created_at)}</span>
                </div>
              </div>

              {/* Ações */}
              <div className="flex flex-wrap items-center gap-2.5">
                <Link
                  to={`/empresa/vagas/${job.id}/candidaturas`}
                  className="btn-outline btn-sm text-xs rounded-xl no-underline inline-flex items-center gap-1.5"
                >
                  <Users className="w-4 h-4" />
                  Candidatos ({job.candidaturas_count ?? job.applications_count ?? 0})
                </Link>

                {job.status !== 'concluida' && job.status !== 'expirada' && (
                  <button
                    onClick={() => handleToggleStatus(job.id)}
                    className="btn-ghost btn-sm text-xs rounded-xl inline-flex items-center gap-1.5"
                    title={job.status === 'ativa' ? 'Pausar recebimento' : 'Ativar recebimento'}
                  >
                    <Power className="w-4 h-4" />
                    {job.status === 'ativa' ? 'Pausar' : 'Reativar'}
                  </button>
                )}

                {job.status === 'ativa' && (
                  <button
                    onClick={() => handleConclude(job.id)}
                    className="btn-ghost btn-sm text-xs rounded-xl inline-flex items-center gap-1.5 text-success-700 hover:bg-success-50"
                  >
                    <Check className="w-4 h-4" />
                    Concluir
                  </button>
                )}

                <button
                  onClick={() => handleOpenEdit(job)}
                  className="btn-ghost btn-sm text-xs rounded-xl"
                >
                  Editar
                </button>
                <button
                  onClick={() => handleOpenDelete(job)}
                  className="btn-danger btn-sm text-xs rounded-xl text-white font-semibold"
                >
                  Deletar
                </button>
              </div>
            </article>
          ))}

          {/* Paginação */}
          {lastPage > 1 && (
            <div className="flex items-center justify-center gap-2 mt-8">
              <button
                onClick={() => handlePageChange(page - 1)}
                disabled={page === 1}
                className="p-2.5 rounded-xl border border-surface-200 bg-white text-slate-600 hover:bg-surface-55 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <ChevronLeft className="w-5 h-5" />
              </button>
              <span className="text-sm font-semibold text-slate-700 px-4 py-2 border border-surface-200 rounded-xl bg-white">
                Página {page} de {lastPage}
              </span>
              <button
                onClick={() => handlePageChange(page + 1)}
                disabled={page === lastPage}
                className="p-2.5 rounded-xl border border-surface-200 bg-white text-slate-600 hover:bg-surface-55 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <ChevronRight className="w-5 h-5" />
              </button>
            </div>
          )}
        </div>
      )}

      {/* Modal Formulário (Criar/Editar Vaga) */}
      <Modal
        isOpen={formModalOpen}
        onClose={() => setFormModalOpen(false)}
        title={editingJob ? 'Editar Vaga' : 'Publicar Nova Vaga'}
        size="xl"
      >
        <form onSubmit={handleSubmit(handleFormSubmit)} className="space-y-4 max-h-[75vh] overflow-y-auto pr-2 scrollbar-hide">
          <Input
            label="Título da Vaga"
            placeholder="Ex: Desenvolvedor React Sênior"
            required
            error={errors.titulo?.message}
            {...register('titulo', { required: 'O título da vaga é obrigatório.' })}
          />

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="label">Área Profissional</label>
              <select
                className="input"
                required
                {...register('area', { required: 'Selecione a área.' })}
              >
                <option value="">Selecione...</option>
                {AREAS.map((a) => (
                  <option key={a} value={a}>{a}</option>
                ))}
              </select>
            </div>

            <Select
              label="Nível"
              options={NIVEIS}
              required
              error={errors.nivel?.message}
              {...register('nivel', { required: 'Selecione o nível.' })}
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Select
              label="Tipo de Contrato"
              options={TIPOS}
              required
              error={errors.tipo?.message}
              {...register('tipo', { required: 'Selecione o tipo.' })}
            />

            <Select
              label="Modalidade"
              options={MODALIDADES}
              required
              error={errors.modalidade?.message}
              {...register('modalidade', { required: 'Selecione a modalidade.' })}
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Input
              label="Cidade"
              placeholder="Ex: Porto Ferreira, SP"
              error={errors.cidade?.message}
              {...register('cidade')}
            />

            <Input
              label="Estado (Sigla)"
              placeholder="Ex: RS"
              maxLength={2}
              error={errors.estado?.message}
              {...register('estado', {
                maxLength: { value: 2, message: 'Digite apenas a sigla (ex: RS)' },
              })}
            />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Input
              label="Salário Mínimo (R$)"
              type="number"
              placeholder="Ex: 4000"
              error={errors.salario_min?.message}
              {...register('salario_min')}
            />

            <Input
              label="Salário Máximo (R$)"
              type="number"
              placeholder="Ex: 6000"
              error={errors.salario_max?.message}
              {...register('salario_max')}
            />
          </div>

          <div>
            <label className="label">Descrição da Vaga</label>
            <textarea
              placeholder="Descreva as responsabilidades e o dia a dia da função..."
              className="input h-32 resize-none"
              required
              {...register('descricao', { required: 'A descrição da vaga é obrigatória.' })}
            />
          </div>

          <div>
            <label className="label">Requisitos</label>
            <textarea
              placeholder="Quais qualificações, experiências e habilidades são fundamentais..."
              className="input h-24 resize-none"
              {...register('requisitos')}
            />
          </div>

          <div>
            <label className="label">Diferenciais</label>
            <textarea
              placeholder="O que destacaria o candidato dos demais (ex: pós-graduação, certificações)..."
              className="input h-24 resize-none"
              {...register('diferenciais')}
            />
          </div>

          <div className="flex gap-3 pt-4 border-t border-surface-200">
            <Button type="button" variant="outline" fullWidth onClick={() => setFormModalOpen(false)}>
              Cancelar
            </Button>
            <Button type="submit" variant="primary" fullWidth loading={formLoading}>
              Salvar Vaga
            </Button>
          </div>
        </form>
      </Modal>

      {/* Modal Deletar */}
      <Modal isOpen={deleteModalOpen} onClose={() => setDeleteModalOpen(false)} title="Excluir Vaga">
        <div className="space-y-4">
          <p className="text-sm text-slate-600 leading-relaxed">
            Deseja mesmo remover permanentemente a vaga <span className="font-bold text-slate-800">"{deletingJob?.titulo}"</span>? Essa ação não pode ser desfeita e todas as candidaturas a esta vaga serão deletadas.
          </p>
          <div className="flex gap-3 pt-2">
            <Button type="button" variant="outline" fullWidth onClick={() => setDeleteModalOpen(false)}>
              Cancelar
            </Button>
            <Button type="button" variant="danger" fullWidth loading={deleteLoading} onClick={handleDeleteSubmit}>
              Excluir Vaga
            </Button>
          </div>
        </div>
      </Modal>
    </div>
  )
}
