import client from './client'

/**
 * favorites.js — Endpoints de favoritos da API ContrataPorto
 */

/**
 * Lista todas as vagas favoritadas pelo candidato autenticado.
 */
export const list = () => client.get('/favorites')

/**
 * Adiciona uma vaga aos favoritos.
 * @param {number|string} jobId
 */
export const add = (jobId) => client.post('/favorites', { job_id: jobId })

/**
 * Remove uma vaga dos favoritos pelo ID da vaga.
 * @param {number|string} jobId
 */
export const remove = (jobId) => client.delete(`/favorites/${jobId}`)
