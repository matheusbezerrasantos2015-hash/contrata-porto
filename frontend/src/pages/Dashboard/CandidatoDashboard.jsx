import { useEffect, useState } from 'react'
import * as applicationsApi from '@/api/applications'
import { FileText, Calendar, Building2, ChevronLeft, ChevronRight, AlertCircle, Heart } from 'lucide-react'
import { Link } from 'react-router-dom'
import Badge from '@/components/Badge'
import Spinner from '@/components/Spinner'
import EmptyState from '@/components/EmptyState'
import {
  formatDate,
  getStatusLabel,
  getStatusColor,
} from '@/utils/formatters'

export default function CandidatoDashboard() {
  const [applications, setApplications] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [page, setPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)

  useEffect(() => {
    setLoading(true)
    setError('')
    applicationsApi
      .myApplications(page)
      .then((res) => {
        const { data, last_page } = res.data?.data ?? {}
        setApplications(data ?? [])
        setLastPage(last_page ?? 1)
      })
      .catch((err) => {
        setError(err.response?.data?.message ?? 'Erro ao carregar candidaturas.')
      })
      .finally(() => setLoading(false))
  }, [page])

  const handlePageChange = (newPage) => {
    if (newPage >= 1 && newPage <= lastPage) {
      setPage(newPage)
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }

  return (
    <div className="page-wrapper py-10">
      <div className="mb-8">
        <h1 className="text-2xl sm:text-3xl font-bold text-primary-600 font-sans">Minhas Candidaturas</h1>
        <p className="text-slate-500 text-sm mt-1">Acompanhe o andamento dos processos seletivos nos quais você se inscreveu.</p>
      </div>

      {error && (
        <div className="mb-6 p-4 bg-danger-50 border border-danger-500/30 rounded-xl text-danger-700 text-sm flex items-start gap-2.5">
          <AlertCircle className="w-5 h-5 flex-shrink-0 mt-0.5 text-danger-500" />
          <span>{error}</span>
        </div>
      )}

      {loading ? (
        <Spinner size="lg" />
      ) : applications.length === 0 ? (
        <EmptyState
          icon={<FileText className="w-12 h-12 text-slate-300" />}
          title="Nenhuma candidatura registrada"
          description="Você ainda não enviou currículos para nenhuma vaga ativa no ContrataPorto."
          action={
            <Link to="/vagas" className="btn-primary btn-md no-underline inline-flex items-center gap-1.5 rounded-xl">
              Buscar Vagas
            </Link>
          }
        />
      ) : (
        <div className="space-y-4">
          {applications.map((app) => (
            <article key={app.id} className="card p-6 bg-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border border-surface-200 shadow-card">
              <div className="flex items-center gap-4 min-w-0">
                <div className="w-12 h-12 rounded-xl bg-primary-50 border border-primary-100 flex items-center justify-center flex-shrink-0">
                  <Building2 className="w-6 h-6 text-primary-400" />
                </div>
                <div className="min-w-0">
                  <h3 className="text-base font-bold text-slate-800 truncate leading-snug">
                    <Link to={`/vagas/${app.vaga?.id ?? app.vaga_id}`} className="hover:underline text-slate-800">
                      {app.vaga?.titulo ?? 'Vaga Indisponível'}
                    </Link>
                  </h3>
                  <p className="text-xs text-slate-500 mt-1 flex items-center gap-1">
                    <Building2 className="w-3.5 h-3.5" />
                    {app.vaga?.empresa?.nome_fantasia ?? 'Empresa Confidencial'}
                  </p>
                  <p className="text-xs text-slate-400 mt-1 flex items-center gap-1">
                    <Calendar className="w-3.5 h-3.5" />
                    Candidatado em {formatDate(app.created_at)}
                  </p>
                </div>
              </div>

              <div className="flex items-center gap-3 self-stretch sm:self-auto justify-between sm:justify-end">
                <span className={`inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold border ${getStatusColor(app.status)}`}>
                  {getStatusLabel(app.status)}
                </span>
                <Link
                  to={`/vagas/${app.vaga?.id ?? app.vaga_id}`}
                  className="btn-outline btn-sm text-xs rounded-xl no-underline"
                >
                  Ver Vaga
                </Link>
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
                aria-label="Página anterior"
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
                aria-label="Próxima página"
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
