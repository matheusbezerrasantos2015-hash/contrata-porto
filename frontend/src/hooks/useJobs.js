import { useCallback, useEffect, useRef, useState } from 'react'
import * as jobsApi from '@/api/jobs'

function serializeFilters(filters = {}) {
  return JSON.stringify(
    Object.fromEntries(
      Object.entries(filters)
        .filter(([, v]) => v !== undefined && v !== null && v !== '')
        .sort(([a], [b]) => a.localeCompare(b))
    )
  )
}

function hasActiveFilters(filters = {}) {
  return Object.values(filters).some(
    (v) => v !== undefined && v !== null && v !== ''
  )
}

/**
 * useJobs — Hook para listagem e filtragem de vagas públicas.
 *
 * @param {Object} filters - Filtros atuais (geralmente derivados da URL)
 * @param {number} perPage - Itens por página
 */
export function useJobs(filters = {}, perPage = 12) {
  const [jobs, setJobs] = useState([])
  const [total, setTotal] = useState(0)
  const [page, setPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  const filtersKey = serializeFilters(filters)
  const filtersActive = hasActiveFilters(filters)
  const prevFiltersKey = useRef(filtersKey)

  // Volta para página 1 quando os filtros mudam
  useEffect(() => {
    if (prevFiltersKey.current !== filtersKey) {
      prevFiltersKey.current = filtersKey
      setPage(1)
    }
  }, [filtersKey])

  const fetchJobs = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const parsedFilters = JSON.parse(filtersKey)
      const res = filtersActive
        ? await jobsApi.filter(parsedFilters, page, perPage)
        : await jobsApi.list(page, perPage)

      const { vagas, total: apiTotal, last_page } = res.data?.data ?? {}
      setJobs(vagas ?? [])
      setTotal(apiTotal ?? 0)
      setLastPage(last_page ?? 1)
    } catch (err) {
      setError(err?.response?.data?.message ?? 'Erro ao carregar vagas.')
    } finally {
      setLoading(false)
    }
  }, [filtersKey, filtersActive, page, perPage])

  useEffect(() => {
    fetchJobs()
  }, [fetchJobs])

  return {
    jobs,
    total,
    page,
    lastPage,
    loading,
    error,
    setPage,
    refetch: fetchJobs,
  }
}

export default useJobs
