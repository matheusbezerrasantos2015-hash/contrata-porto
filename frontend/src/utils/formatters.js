/**
 * utils/formatters.js — Funções de formatação do ContrataPorto
 */

/**
 * Formata um valor numérico como moeda BRL ou uma faixa de salários.
 * @param {number|string|null} min
 * @param {number|string|null} max
 * @returns {string}
 */
export function formatSalary(min, max = null) {
  const formatSingle = (value) => {
    if (value === null || value === undefined || value === '') return ''
    const num = parseFloat(value)
    if (isNaN(num)) return ''
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(num)
  }

  const minFormatted = formatSingle(min)
  const maxFormatted = formatSingle(max)

  if (!minFormatted && !maxFormatted) return 'A combinar'
  if (minFormatted && !maxFormatted) return `A partir de ${minFormatted}`
  if (!minFormatted && maxFormatted) return `Até ${maxFormatted}`
  if (minFormatted === maxFormatted) return minFormatted
  return `${minFormatted} – ${maxFormatted}`
}

/**
 * Mantido para retrocompatibilidade caso outro arquivo o utilize.
 */
export function formatSalaryRange(min, max) {
  return formatSalary(min, max)
}

// ─── Datas ──────────────────────────────────────────────────────────────────

/**
 * Formata uma data ISO para exibição no padrão brasileiro.
 * @param {string|Date} date
 * @param {Object} options - opções para Intl.DateTimeFormat
 * @returns {string}
 */
export function formatDate(date, options = {}) {
  if (!date) return '—'
  const d = date instanceof Date ? date : new Date(date)
  if (isNaN(d.getTime())) return '—'
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    ...options,
  }).format(d)
}

/**
 * Retorna uma data relativa amigável ao usuário ("há 2 dias", "há 1 hora").
 * @param {string|Date} date
 * @returns {string}
 */
export function timeAgo(date) {
  if (!date) return ''
  const d = date instanceof Date ? date : new Date(date)
  if (isNaN(d.getTime())) return ''
  const diff = (Date.now() - d.getTime()) / 1000 // diferença em segundos

  if (diff < 60)    return 'agora mesmo'
  if (diff < 3600)  return `há ${Math.floor(diff / 60)} min`
  if (diff < 86400) return `há ${Math.floor(diff / 3600)} h`
  if (diff < 604800) return `há ${Math.floor(diff / 86400)} dias`
  return formatDate(d)
}

// ─── Status de Vagas e Candidaturas ──────────────────────────────────────────

const STATUS_LABELS = {
  // Vaga
  ativa:     'Ativa',
  pausada:   'Pausada',
  concluida: 'Concluída',
  expirada:  'Expirada',
  // Candidatura
  pendente:   'Pendente',
  em_analise: 'Em análise',
  aprovado:   'Aprovado',
  recusado:   'Recusado',
}

const STATUS_COLORS = {
  // Vaga & Candidatura
  ativa:      'bg-success-50 text-success-700 border-success-200',
  aprovado:   'bg-success-50 text-success-700 border-success-200',
  pausada:    'bg-warning-50 text-warning-700 border-warning-200',
  em_analise: 'bg-warning-50 text-warning-700 border-warning-200',
  concluida:  'bg-slate-100 text-slate-600 border-slate-200',
  pendente:   'bg-slate-100 text-slate-600 border-slate-200',
  expirada:   'bg-danger-50 text-danger-700 border-danger-200',
  recusado:   'bg-danger-50 text-danger-700 border-danger-200',
}

/**
 * Retorna o rótulo amigável para qualquer status da plataforma.
 * @param {string} status
 * @returns {string}
 */
export function getStatusLabel(status) {
  return STATUS_LABELS[status] ?? status
}

/**
 * Retorna as classes de cores do Tailwind baseadas no status.
 * @param {string} status
 * @returns {string}
 */
export function getStatusColor(status) {
  return STATUS_COLORS[status] ?? 'bg-primary-50 text-primary-700 border-primary-200'
}

/**
 * Métodos legados mantidos para compatibilidade com importações anteriores.
 */
export function formatJobStatus(status) {
  return getStatusLabel(status)
}

export function formatApplicationStatus(status) {
  return getStatusLabel(status)
}

export function applicationStatusVariant(status) {
  switch (status) {
    case 'aprovado':   return 'success'
    case 'em_analise': return 'warning'
    case 'recusado':   return 'danger'
    case 'pendente':   return 'neutral'
    default:           return 'primary'
  }
}

export function jobStatusVariant(status) {
  switch (status) {
    case 'ativa':      return 'success'
    case 'pausada':    return 'warning'
    case 'concluida':  return 'neutral'
    case 'expirada':   return 'danger'
    default:           return 'neutral'
  }
}


// ─── Outros ─────────────────────────────────────────────────────────────────

/**
 * Formata a modalidade de trabalho de forma legível.
 * @param {string} modalidade
 * @returns {string}
 */
export function formatModalidade(modalidade) {
  const map = {
    presencial: 'Presencial',
    remoto:     'Remoto',
    hibrido:    'Híbrido',
  }
  return map[modalidade] ?? modalidade
}

/**
 * Formata o tipo de contrato de forma legível.
 * @param {string} tipo
 * @returns {string}
 */
export function formatTipoContrato(tipo) {
  const map = {
    CLT:          'CLT',
    PJ:           'PJ',
    Freelancer:   'Freelancer',
    Estágio:      'Estágio',
    Temporário:   'Temporário',
    Jovem_Aprendiz: 'Jovem Aprendiz',
  }
  return map[tipo] ?? tipo
}

/**
 * Retorna as iniciais de um nome (ex: "Maria Silva" → "MS").
 * @param {string} name
 * @returns {string}
 */
export function getInitials(name = '') {
  return name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((word) => word[0]?.toUpperCase() ?? '')
    .join('')
}
