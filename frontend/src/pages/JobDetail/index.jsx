import { useNavigate, useParams, Link } from 'react-router-dom'
import { useEffect, useState } from 'react'
import * as jobsApi from '@/api/jobs'
import * as applicationsApi from '@/api/applications'
import { useAuth } from '@/contexts/AuthContext'
import { useFavorites } from '@/hooks/useFavorites'
import {
  Briefcase,
  MapPin,
  Clock,
  Building2,
  Heart,
  Banknote,
  Calendar,
  AlertCircle,
  FileText,
  CheckCircle2,
  ChevronLeft,
  GraduationCap,
} from 'lucide-react'
import { useForm } from 'react-hook-form'
import Badge from '@/components/Badge'
import Button from '@/components/Button'
import Modal from '@/components/Modal'
import Input from '@/components/Input'
import Spinner from '@/components/Spinner'
import {
  formatSalary,
  formatModalidade,
  formatTipoContrato,
  formatDate,
  jobStatusVariant,
  getStatusLabel,
} from '@/utils/formatters'

export default function JobDetail() {
  const { id } = useParams()
  const navigate = useNavigate()
  const { isAuthenticated, userType } = useAuth()
  const { toggle, isFavorite } = useFavorites()

  const [job, setJob] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  // Candidatura
  const [modalOpen, setModalOpen] = useState(false)
  const [applyLoading, setApplyLoading] = useState(false)
  const [applySuccess, setApplySuccess] = useState(false)
  const [applyError, setApplyError] = useState('')
  const [selectedFile, setSelectedFile] = useState(null)

  const { register, handleSubmit, formState: { errors }, reset } = useForm()

  useEffect(() => {
    setLoading(true)
    jobsApi
      .show(id)
      .then((res) => {
        setJob(res.data?.data ?? null)
      })
      .catch((err) => {
        setError(err.response?.data?.message ?? 'Não foi possível carregar os detalhes da vaga.')
      })
      .finally(() => setLoading(false))
  }, [id])

  const handleApplyClick = () => {
    if (!isAuthenticated) {
      navigate('/login', { state: { from: { pathname: `/vagas/${id}` } } })
      return
    }
    setModalOpen(true)
  }

  const onApplySubmit = async (data) => {
    if (!selectedFile) {
      setApplyError('Por favor, faça o upload de seu currículo em formato PDF.')
      return
    }
    setApplyLoading(true)
    setApplyError('')
    try {
      const payload = {
        vaga_id: job.id,
        job_id: job.id,
        mensagem: data.mensagem,
        linkedin: data.linkedin,
        portfolio: data.portfolio,
        telefone: data.telefone,
      }
      await applicationsApi.apply(payload, selectedFile)
      setApplySuccess(true)
      reset()
      setSelectedFile(null)
      setTimeout(() => {
        setModalOpen(false)
        setApplySuccess(false)
      }, 3000)
    } catch (err) {
      setApplyError(err.response?.data?.message ?? 'Erro ao enviar candidatura. Você já pode ter se candidatado a esta vaga.')
    } finally {
      setApplyLoading(false)
    }
  }

  if (loading) {
    return <Spinner size="lg" fullPage />
  }

  if (error || !job) {
    return (
      <div className="page-wrapper py-20 text-center">
        <AlertCircle className="w-12 h-12 text-danger-500 mx-auto mb-4" />
        <h2 className="text-xl font-bold text-slate-800">Vaga não encontrada</h2>
        <p className="text-slate-500 text-sm mt-1">{error ?? 'Esta vaga pode ter sido removida ou concluída.'}</p>
        <Link to="/vagas" className="btn-primary btn-md mt-6 no-underline inline-flex items-center gap-2">
          Voltar para Vagas
        </Link>
      </div>
    )
  }

  const isCandidate = isAuthenticated && userType === 'CANDIDATO'
  const favorited = isFavorite(job.id)
  const salaryLabel = formatSalary(job.salario_min, job.salario_max)

  return (
    <div className="page-wrapper py-10">
      {/* Botão voltar */}
      <button
        onClick={() => navigate(-1)}
        className="flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-slate-700 mb-6 transition-colors"
      >
        <ChevronLeft className="w-4 h-4" />
        Voltar
      </button>

      {/* Grid Principal */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        {/* Coluna Detalhes */}
        <div className="lg:col-span-2 space-y-6">
          <div className="card p-6 sm:p-8 bg-white">
            <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-surface-200">
              <div className="flex items-center gap-4">
                <div className="w-16 h-16 rounded-2xl bg-primary-50 border border-primary-100 flex items-center justify-center flex-shrink-0">
                  <Building2 className="w-8 h-8 text-primary-400" />
                </div>
                <div>
                  <h1 className="text-2xl font-bold text-primary-600 leading-tight">{job.titulo}</h1>
                  <p className="text-slate-600 font-medium text-sm mt-1">
                    {job.empresa?.nome_fantasia ?? 'Empresa Confidencial'}
                  </p>
                </div>
              </div>

              {/* Status ou Favoritos */}
              <div className="flex items-center gap-2 self-stretch sm:self-auto justify-end">
                <Badge variant={jobStatusVariant(job.status)}>
                  {getStatusLabel(job.status)}
                </Badge>
                {isCandidate && (
                  <button
                    onClick={() => toggle(job.id)}
                    className={`p-2.5 rounded-xl border border-surface-200 transition-colors ${
                      favorited
                        ? 'text-accent-500 bg-accent-50 border-accent-200'
                        : 'text-slate-400 hover:text-accent-400 hover:bg-surface-50'
                    }`}
                    aria-label={favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'}
                  >
                    <Heart className={`w-5 h-5 ${favorited && 'fill-current'}`} />
                  </button>
                )}
              </div>
            </div>

            {/* Chips rápidos */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-6">
              <div className="flex items-center gap-2.5">
                <MapPin className="w-5 h-5 text-slate-400" />
                <div>
                  <p className="text-xs text-slate-400 font-medium uppercase">Cidade</p>
                  <p className="text-sm font-semibold text-slate-700 truncate">
                    {job.modalidade === 'remoto' ? 'Remoto' : (job.cidade ?? 'Porto Alegre')}
                  </p>
                </div>
              </div>
              <div className="flex items-center gap-2.5">
                <Clock className="w-5 h-5 text-slate-400" />
                <div>
                  <p className="text-xs text-slate-400 font-medium uppercase">Contrato</p>
                  <p className="text-sm font-semibold text-slate-700 truncate">{formatTipoContrato(job.tipo)}</p>
                </div>
              </div>
              <div className="flex items-center gap-2.5">
                <Briefcase className="w-5 h-5 text-slate-400" />
                <div>
                  <p className="text-xs text-slate-400 font-medium uppercase">Modalidade</p>
                  <p className="text-sm font-semibold text-slate-700 truncate">{formatModalidade(job.modalidade)}</p>
                </div>
              </div>
              <div className="flex items-center gap-2.5">
                <Banknote className="w-5 h-5 text-slate-400" />
                <div>
                  <p className="text-xs text-slate-400 font-medium uppercase">Salário</p>
                  <p className="text-sm font-semibold text-slate-700 truncate">{salaryLabel}</p>
                </div>
              </div>
            </div>
          </div>

          <div className="card p-6 sm:p-8 bg-white space-y-6">
            <div>
              <h2 className="text-lg font-bold text-primary-600 mb-3">Descrição da Vaga</h2>
              <div className="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
                {job.descricao}
              </div>
            </div>

            {job.requisitos && (
              <div className="border-t border-surface-200 pt-6">
                <h2 className="text-lg font-bold text-primary-600 mb-3">Requisitos</h2>
                <div className="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
                  {job.requisitos}
                </div>
              </div>
            )}

            {job.diferenciais && (
              <div className="border-t border-surface-200 pt-6">
                <h2 className="text-lg font-bold text-primary-600 mb-3">Diferenciais</h2>
                <div className="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
                  {job.diferenciais}
                </div>
              </div>
            )}
          </div>
        </div>

        {/* Coluna Sidebar */}
        <div className="space-y-6">
          {/* Caixa de Candidatura */}
          <div className="card p-6 bg-white border-primary-100 flex flex-col gap-5">
            <h3 className="font-bold text-slate-800 text-base">Candidatura</h3>
            <p className="text-slate-500 text-xs leading-relaxed">
              Ao se candidatar, seu perfil e currículo serão compartilhados com os recrutadores da vaga.
            </p>

            {job.status !== 'ativa' ? (
              <div className="p-3 bg-surface-100 border border-surface-200 rounded-xl text-center text-xs font-semibold text-slate-500">
                Esta vaga não está aceitando candidaturas.
              </div>
            ) : isCandidate || !isAuthenticated ? (
              <Button onClick={handleApplyClick} variant="accent" fullWidth>
                Candidatar-se para a vaga
              </Button>
            ) : (
              <div className="p-3 bg-surface-100 border border-surface-200 rounded-xl text-center text-xs font-semibold text-slate-500">
                Apenas perfis de candidatos podem se candidatar.
              </div>
            )}
          </div>

          {/* Informações da Empresa */}
          <div className="card p-6 bg-white space-y-4">
            <h3 className="font-bold text-slate-800 text-base">Sobre a Empresa</h3>
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-lg bg-surface-100 border border-surface-200 flex items-center justify-center">
                <Building2 className="w-5 h-5 text-slate-400" />
              </div>
              <p className="font-semibold text-sm text-slate-700 truncate">
                {job.empresa?.nome_fantasia ?? 'Empresa Confidencial'}
              </p>
            </div>
            {job.empresa?.descricao && (
              <p className="text-slate-500 text-xs leading-relaxed line-clamp-4">
                {job.empresa.descricao}
              </p>
            )}
            {job.empresa?.site && (
              <a
                href={job.empresa.site}
                target="_blank"
                rel="noopener noreferrer"
                className="text-xs font-semibold text-primary-600 hover:text-primary-700 flex items-center gap-1 no-underline"
              >
                Visitar site da empresa
              </a>
            )}
          </div>

          {/* Dados Gerais da Vaga */}
          <div className="card p-6 bg-white space-y-3">
            <h3 className="font-bold text-slate-800 text-base mb-1">Informações Adicionais</h3>

            <div className="flex justify-between items-center text-xs">
              <span className="text-slate-400 font-medium flex items-center gap-1.5">
                <Calendar className="w-4 h-4" /> Publicada em
              </span>
              <span className="font-semibold text-slate-700">{formatDate(job.created_at)}</span>
            </div>
            {job.area && (
              <div className="flex justify-between items-center text-xs">
                <span className="text-slate-400 font-medium flex items-center gap-1.5">
                  <GraduationCap className="w-4 h-4" /> Área
                </span>
                <span className="font-semibold text-slate-700">{job.area}</span>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Modal de Candidatura */}
      <Modal isOpen={modalOpen} onClose={() => setModalOpen(false)} title="Candidatura para a Vaga">
        {applySuccess ? (
          <div className="text-center py-6">
            <div className="w-16 h-16 bg-success-50 text-success-500 rounded-full flex items-center justify-center mx-auto mb-4 border border-success-200">
              <CheckCircle2 className="w-8 h-8" />
            </div>
            <h3 className="text-lg font-bold text-slate-800">Candidatura enviada!</h3>
            <p className="text-slate-500 text-sm mt-1">Sua candidatura foi registrada com sucesso.</p>
          </div>
        ) : (
          <form onSubmit={handleSubmit(onApplySubmit)} className="space-y-4">
            {applyError && (
              <div className="p-3 bg-danger-50 border border-danger-500/30 rounded-xl text-danger-700 text-xs flex items-start gap-2">
                <AlertCircle className="w-4 h-4 flex-shrink-0 mt-0.5" />
                <span>{applyError}</span>
              </div>
            )}

            <Input
              label="Telefone para contato"
              type="tel"
              placeholder="(51) 99999-9999"
              required
              error={errors.telefone?.message}
              {...register('telefone', { required: 'O telefone é obrigatório' })}
            />

            <Input
              label="LinkedIn URL"
              type="url"
              placeholder="https://linkedin.com/in/seu-perfil"
              error={errors.linkedin?.message}
              {...register('linkedin', {
                pattern: {
                  value: /^https?:\/\/(www\.)?linkedin\.com\/.*$/i,
                  message: 'Insira uma URL válida do LinkedIn',
                },
              })}
            />

            <Input
              label="Portfólio URL (opcional)"
              type="url"
              placeholder="https://seuportfolioweb.com"
              error={errors.portfolio?.message}
              {...register('portfolio')}
            />

            <div>
              <label className="label">Mensagem de apresentação (opcional)</label>
              <textarea
                placeholder="Escreva uma breve mensagem aos recrutadores..."
                className="input h-24 resize-none"
                {...register('mensagem')}
              />
            </div>

            {/* Currículo PDF File Upload */}
            <div>
              <label className="label">
                Upload do Currículo (PDF) <span className="text-danger-500">*</span>
              </label>
              <div className="border-2 border-dashed border-surface-300 rounded-xl p-6 text-center hover:border-primary-500 transition-colors bg-white relative cursor-pointer">
                <input
                  type="file"
                  accept=".pdf"
                  className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                  onChange={(e) => setSelectedFile(e.target.files?.[0] ?? null)}
                  required
                />
                <FileText className="w-8 h-8 text-slate-400 mx-auto mb-2" />
                {selectedFile ? (
                  <p className="text-xs font-semibold text-primary-600 truncate px-4">
                    {selectedFile.name} ({(selectedFile.size / 1024 / 1024).toFixed(2)} MB)
                  </p>
                ) : (
                  <>
                    <p className="text-xs text-slate-600 font-semibold">Arraste ou clique para selecionar</p>
                    <p className="text-[10px] text-slate-400 mt-1">Apenas arquivos no formato PDF (máx. 5MB)</p>
                  </>
                )}
              </div>
            </div>

            <div className="flex gap-3 pt-2">
              <Button type="button" variant="outline" fullWidth onClick={() => setModalOpen(false)}>
                Cancelar
              </Button>
              <Button type="submit" variant="primary" fullWidth loading={applyLoading}>
                Enviar Candidatura
              </Button>
            </div>
          </form>
        )}
      </Modal>
    </div>
  )
}
