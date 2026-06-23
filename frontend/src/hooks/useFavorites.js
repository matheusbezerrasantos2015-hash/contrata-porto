import { useCallback, useEffect, useState } from 'react'
import * as favoritesApi from '@/api/favorites'
import { useAuth } from '@/contexts/AuthContext'

/**
 * useFavorites — Gerencia as vagas favoritas do candidato autenticado.
 *
 * @returns {{ favorites, favoriteIds, loading, toggle, isFavorite }}
 */
export function useFavorites() {
  const { isAuthenticated, userType } = useAuth()
  const [favorites, setFavorites] = useState([])
  const [loading, setLoading] = useState(false)

  const canFavorite = isAuthenticated && userType === 'CANDIDATO'

  // ─── Carregar favoritos ao montar ──────────────────────────────────────────
  useEffect(() => {
    if (!canFavorite) return

    setLoading(true)
    favoritesApi
      .list()
      .then((res) => setFavorites(res.data?.data?.vagas ?? []))
      .catch(() => setFavorites([]))
      .finally(() => setLoading(false))
  }, [canFavorite])

  // ─── IDs de vagas favoritas para lookup O(1) ───────────────────────────────
  const favoriteIds = new Set(favorites.map((j) => j.id))

  // ─── Toggle favorito ───────────────────────────────────────────────────────
  const toggle = useCallback(
    async (jobId) => {
      if (!canFavorite) return

      const isFav = favoriteIds.has(jobId)
      // Otimistic update
      setFavorites((prev) =>
        isFav ? prev.filter((j) => j.id !== jobId) : [...prev, { id: jobId }]
      )

      try {
        if (isFav) {
          await favoritesApi.remove(jobId)
        } else {
          await favoritesApi.add(jobId)
        }
      } catch (_) {
        // Rollback em caso de erro
        setFavorites((prev) =>
          isFav ? [...prev, { id: jobId }] : prev.filter((j) => j.id !== jobId)
        )
      }
    },
    [canFavorite, favoriteIds]
  )

  const isFavorite = useCallback((jobId) => favoriteIds.has(jobId), [favoriteIds])

  return { favorites, favoriteIds, loading, toggle, isFavorite }
}

export default useFavorites
