import { useCallback, useEffect, useState } from 'react'
import * as jobsApi from '@/api/jobs'

/**
 * useJobs — Hook para listagem e filtragem de vagas públicas.
 *
 * @param {Object} initialFilters - Filtros iniciais opcionais
 * @returns {{ jobs, total, page, lastPage, loading, error, setPage, setFilters, refetch }}
 */
export function useJobs(initialFilters = {}) {
  const [jobs, setJobs] = useState([])
  const [total, setTotal] = useState(0)
  const [page, setPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [filters, setFilters] = useState(initialFilters)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  const hasFilters = Object.values(filters).some(
    (v) => v !== undefined && v !== null && v !== ''
  )

  const fetchJobs = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const res = hasFilters
        ? await jobsApi.filter(filters, page)
        : await jobsApi.list(page)

      const { vagas, total, last_page, current_page } = res.data?.data ?? {}
      setJobs(vagas ?? [])
      setTotal(total ?? 0)
      setLastPage(last_page ?? 1)
      setPage(current_page ?? page)
    } catch (err) {
      setError(err?.response?.data?.message ?? 'Erro ao carregar vagas.')
    } finally {
      setLoading(false)
    }
  }, [filters, page, hasFilters])

  useEffect(() => {
    fetchJobs()
  }, [fetchJobs])

  const applyFilters = useCallback((newFilters) => {
    setFilters(newFilters)
    setPage(1)
  }, [])

  return {
    jobs,
    total,
    page,
    lastPage,
    loading,
    error,
    setPage,
    setFilters: applyFilters,
    refetch: fetchJobs,
  }
}

export default useJobs
