import { useNavigate } from 'react-router-dom'
import {
  Briefcase,
  MapPin,
  Clock,
  Building2,
  Heart,
  Banknote,
} from 'lucide-react'
import Badge from './Badge'
import {
  formatSalary,
  formatModalidade,
  formatTipoContrato,
  timeAgo,
  jobStatusVariant,
  getStatusLabel,
} from '@/utils/formatters'
import { useAuth } from '@/contexts/AuthContext'
import { useFavorites } from '@/hooks/useFavorites'
import clsx from 'clsx'

/**
 * JobCard — card de exibição de uma vaga.
 *
 * @prop {Object}  job - objeto da vaga
 * @prop {boolean} showStatus - exibe o badge de status (para empresa)
 * @prop {boolean} showActions - exibe botões de editar/excluir (para empresa)
 * @prop {Function} onEdit
 * @prop {Function} onDelete
 */
export default function JobCard({ job, showStatus = false, showActions = false, onEdit, onDelete }) {
  const { isAuthenticated, userType } = useAuth()
  const { toggle, isFavorite } = useFavorites()
  const navigate = useNavigate()

  const isCandidate   = isAuthenticated && userType === 'CANDIDATO'
  const favorited     = isFavorite(job.id)
  const salaryLabel   = formatSalary(job.salario_min, job.salario_max)
  const modalidade    = formatModalidade(job.modalidade)
  const tipo          = formatTipoContrato(job.tipo)

  const handleCardClick = () => {
    navigate(`/vagas/${job.id}`)
  }

  const handleFavoriteClick = (e) => {
    e.stopPropagation()
    toggle(job.id)
  }

  const handleActionClick = (e, callback) => {
    e.stopPropagation()
    callback?.(job)
  }

  return (
    <article
      onClick={handleCardClick}
      className="card-hover flex flex-col p-5 gap-4 cursor-pointer select-none"
    >
      {/* Header */}
      <div className="flex items-start justify-between gap-3">
        {/* Logo empresa */}
        <div className="flex items-center gap-3 min-w-0">
          <div className="w-12 h-12 rounded-xl bg-primary-50 border border-primary-100 flex items-center justify-center flex-shrink-0">
            <Building2 className="w-6 h-6 text-primary-400" />
          </div>
          <div className="min-w-0">
            <h3 className="text-sm font-semibold text-slate-800 truncate leading-snug">
              {job.titulo}
            </h3>
            <p className="text-xs text-slate-500 truncate mt-0.5">
              {job.empresa?.nome_fantasia ?? '—'}
            </p>
          </div>
        </div>

        {/* Ações */}
        <div className="flex items-center gap-1 flex-shrink-0">
          {showStatus && (
            <Badge variant={jobStatusVariant(job.status)}>
              {getStatusLabel(job.status)}
            </Badge>
          )}
          {isCandidate && (
            <button
              onClick={handleFavoriteClick}
              className={clsx(
                'p-2.5 rounded-lg transition-colors hover:bg-surface-50 tap-target',
                favorited
                  ? 'text-accent-500 hover:text-accent-600'
                  : 'text-slate-300 hover:text-accent-400'
              )}
              aria-label={favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'}
              title={favorited ? 'Remover dos favoritos' : 'Adicionar aos favoritos'}
            >
              <Heart className={clsx('w-5 h-5', favorited && 'fill-current')} />
            </button>
          )}
        </div>
      </div>

      {/* Chips de info */}
      <div className="flex flex-wrap gap-2">
        <span className="inline-flex items-center gap-1 text-xs text-slate-500">
          <MapPin className="w-3.5 h-3.5" />
          {job.modalidade === 'remoto' ? 'Remoto' : (job.cidade ? `${job.cidade}, ${job.estado ?? 'RS'}` : 'Não informado')}
        </span>
        <span className="inline-flex items-center gap-1 text-xs text-slate-500">
          <Briefcase className="w-3.5 h-3.5" />
          {tipo}
        </span>
        <span className="inline-flex items-center gap-1 text-xs text-slate-500">
          <Clock className="w-3.5 h-3.5" />
          {modalidade}
        </span>
        {salaryLabel !== 'A combinar' && (
          <span className="inline-flex items-center gap-1 text-xs text-slate-500">
            <Banknote className="w-3.5 h-3.5" />
            {salaryLabel}
          </span>
        )}
      </div>

      {/* Área */}
      {(job.area || job.nivel) && (
        <div className="flex flex-wrap gap-1.5">
          {job.area && <Badge variant="primary">{job.area}</Badge>}
          {job.nivel && <Badge variant="neutral">{job.nivel}</Badge>}
        </div>
      )}

      {/* Footer */}
      <div className="flex items-center justify-between mt-auto pt-3 border-t border-surface-100">
        <span className="text-xs text-slate-400">{timeAgo(job.created_at)}</span>

        <div className="flex gap-2">
          {showActions && (
            <>
              <button
                onClick={(e) => handleActionClick(e, onEdit)}
                className="btn-ghost btn-sm text-xs px-3"
              >
                Editar
              </button>
              <button
                onClick={(e) => handleActionClick(e, onDelete)}
                className="btn-danger btn-sm text-xs px-3 text-white"
              >
                Excluir
              </button>
            </>
          )}
        </div>
      </div>
    </article>
  )
}
