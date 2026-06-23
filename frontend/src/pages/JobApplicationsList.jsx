import { useNavigate, useParams, Link } from 'react-router-dom'
import { useEffect, useState, useCallback } from 'react'
import * as applicationsApi from '@/api/applications'
import * as jobsApi from '@/api/jobs'
import client from '@/api/client'
import {
  ChevronLeft,
  ChevronRight,
  User,
  Phone,
  Linkedin,
  Globe,
  FileDown,
  Calendar,
  AlertCircle,
  CheckCircle2,
  Mail,
} from 'lucide-react'
import Badge from '@/components/Badge'
import Button from '@/components/Button'
import {
  formatDate,
  formatApplicationStatus,
  applicationStatusVariant,
} from '@/utils/formatters'

export default function JobApplicationsList() {
  const { id: jobId } = useParams()
  const navigate = useNavigate()

  const [job, setJob] = useState(null)
  const [applications, setApplications] = useState([])
  const [loading, setLoading] = useState(true)
  const [errorMsg, setErrorMsg] = useState('')
  const [successMsg, setSuccessMsg] = useState('')

  // Paginação
  const [page, setPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)

  // Status loading por item
  const [updatingId, setUpdatingId] = useState(null)

  const fetchJobDetails = useCallback(async () => {
    try {
      const res = await jobsApi.show(jobId)
      setJob(res.data?.data ?? null)
    } catch (_) {}
  }, [jobId])

  const fetchApplications = useCallback(async () => {
    setLoading(true)
    setErrorMsg('')
    try {
      const res = await applicationsApi.jobApplications(jobId, page)
      const { data, last_page } = res.data?.data ?? {}
      setApplications(data ?? [])
      setLastPage(last_page ?? 1)
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Não foi possível carregar as candidaturas.')
    } finally {
      setLoading(false)
    }
  }, [jobId, page])

  useEffect(() => {
    fetchJobDetails()
    fetchApplications()
  }, [fetchJobDetails, fetchApplications])

  const handleUpdateStatus = async (appId, status) => {
    setUpdatingId(appId)
    setErrorMsg('')
    setSuccessMsg('')
    try {
      await applicationsApi.updateStatus(appId, status)
      setSuccessMsg(`Status do candidato alterado para "${formatApplicationStatus(status)}"`)
      // Atualiza a lista localmente
      setApplications((prev) =>
        prev.map((app) => (app.id === appId ? { ...app, status } : app))
      )
    } catch (err) {
      setErrorMsg(err.response?.data?.message ?? 'Erro ao alterar status da candidatura.')
    } finally {
      setUpdatingId(null)
    }
  }

  const handleDownloadCurriculo = async (appId, candidateName) => {
    try {
      const response = await client.get(`/applications/${appId}/curriculo`, { responseType: 'blob' })
      const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `curriculo-${candidateName.toLowerCase().replace(/\s+/g, '-')}.pdf`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
    } catch (_) {
      setErrorMsg('Não foi possível fazer o download do currículo.')
    }
  }

  const handlePageChange = (newPage) => {
    if (newPage >= 1 && newPage <= lastPage) {
      setPage(newPage)
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }

  if (loading && !job) {
    return (
      <div className="flex items-center justify-center min-h-[50vh]">
        <div className="spinner text-primary-600" />
      </div>
    )
  }

  return (
    <div className="page-wrapper py-10">
      {/* Botão voltar */}
      <button
        onClick={() => navigate('/empresa/vagas')}
        className="flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-slate-700 mb-6 transition-colors"
      >
        <ChevronLeft className="w-4 h-4" />
        Minhas Vagas
      </button>

      {/* Detalhes da Vaga Cabeçalho */}
      {job && (
        <div className="card p-6 bg-white mb-8 border-l-4 border-l-primary-500">
          <div className="flex items-start sm:items-center justify-between gap-4 flex-wrap">
            <div>
              <span className="text-xs font-bold text-primary-500 uppercase tracking-wider">Candidaturas da Vaga</span>
              <h1 className="text-xl sm:text-2xl font-bold text-slate-800 mt-1">{job.titulo}</h1>
              <p className="text-xs text-slate-400 mt-1">
                Publicada em {formatDate(job.created_at)} | Status: {formatJobStatus(job.status)}
              </p>
            </div>
            <Link to={`/vagas/${job.id}`} className="btn-ghost btn-sm text-xs rounded-xl no-underline">
              Visualizar Vaga Pública
            </Link>
          </div>
        </div>
      )}

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
        <div className="space-y-4">
          {[1, 2].map((n) => (
            <div key={n} className="card p-6 h-40 bg-white animate-pulse" />
          ))}
        </div>
      ) : applications.length === 0 ? (
        <div className="card p-12 text-center bg-white max-w-xl mx-auto w-full">
          <User className="w-12 h-12 text-slate-300 mx-auto mb-4" />
          <p className="text-slate-600 font-semibold">Nenhuma candidatura recebida ainda.</p>
          <p className="text-slate-400 text-sm mt-1">Divulgue o link da vaga pública para atrair mais candidatos.</p>
        </div>
      ) : (
        <div className="space-y-6">
          {applications.map((app) => (
            <article key={app.id} className="card p-6 bg-white flex flex-col gap-5 border border-surface-200">
              <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                {/* Dados do Candidato */}
                <div className="space-y-2">
                  <div className="flex items-center gap-2 flex-wrap">
                    <h3 className="text-lg font-bold text-slate-800 leading-snug">
                      {app.candidato?.usuario?.nome ?? 'Candidato'}
                    </h3>
                    <Badge variant={applicationStatusVariant(app.status)}>
                      {formatApplicationStatus(app.status)}
                    </Badge>
                  </div>

                  <div className="flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-slate-500">
                    <span className="flex items-center gap-1">
                      <Mail className="w-3.5 h-3.5" />
                      {app.candidato?.usuario?.email ?? '—'}
                    </span>
                    {app.telefone && (
                      <span className="flex items-center gap-1">
                        <Phone className="w-3.5 h-3.5" />
                        {app.telefone}
                      </span>
                    )}
                    <span className="flex items-center gap-1">
                      <Calendar className="w-3.5 h-3.5" />
                      Enviado em {formatDate(app.created_at)}
                    </span>
                  </div>
                </div>

                {/* Status Picker (Pipeline) */}
                <div className="flex items-center gap-2">
                  <label className="text-xs font-semibold text-slate-400 uppercase hidden lg:block">Alterar Status:</label>
                  <select
                    value={app.status}
                    disabled={updatingId === app.id}
                    onChange={(e) => handleUpdateStatus(app.id, e.target.value)}
                    className="input py-1.5 px-3 text-xs w-auto font-semibold bg-surface-50 border-surface-200 text-slate-700"
                  >
                    <option value="pendente">Pendente</option>
                    <option value="em_analise">Em Análise</option>
                    <option value="aprovado">Aprovar</option>
                    <option value="recusado">Recusar</option>
                  </select>
                </div>
              </div>

              {/* Mensagem de Apresentação */}
              {app.mensagem && (
                <div className="bg-surface-50 p-4 rounded-xl border border-surface-200 text-sm text-slate-600 italic whitespace-pre-line leading-relaxed">
                  "{app.mensagem}"
                </div>
              )}

              {/* Links de Documentos/Portfólios */}
              <div className="flex flex-wrap items-center justify-between gap-4 pt-3 border-t border-surface-100">
                <div className="flex items-center gap-3">
                  {app.linkedin && (
                    <a
                      href={app.linkedin}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 hover:underline no-underline"
                    >
                      <Linkedin className="w-4 h-4" /> LinkedIn
                    </a>
                  )}
                  {app.portfolio && (
                    <a
                      href={app.portfolio}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 hover:underline no-underline"
                    >
                      <Globe className="w-4 h-4" /> Portfólio
                    </a>
                  )}
                </div>

                <Button
                  onClick={() => handleDownloadCurriculo(app.id, app.candidato?.usuario?.nome ?? 'candidato')}
                  variant="primary"
                  size="sm"
                  className="rounded-xl text-xs"
                  leftIcon={<FileDown className="w-4 h-4" />}
                >
                  Download Currículo (PDF)
                </Button>
              </div>
            </article>
          ))}

          {/* Paginação */}
          {lastPage > 1 && (
            <div className="flex items-center justify-center gap-2 mt-8">
              <button
                onClick={() => handlePageChange(page - 1)}
                disabled={page === 1}
                className="p-2.5 rounded-xl border border-surface-200 bg-white text-slate-600 hover:bg-surface-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <ChevronLeft className="w-5 h-5" />
              </button>
              <span className="text-sm font-semibold text-slate-700 px-4 py-2 border border-surface-200 rounded-xl bg-white">
                Página {page} de {lastPage}
              </span>
              <button
                onClick={() => handlePageChange(page + 1)}
                disabled={page === lastPage}
                className="p-2.5 rounded-xl border border-surface-200 bg-white text-slate-600 hover:bg-surface-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <ChevronRight className="w-5 h-5" />
              </button>
            </div>
          )}
        </div>
      )}
    </div>
  )
}
