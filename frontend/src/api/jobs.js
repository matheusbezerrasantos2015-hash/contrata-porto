import client from './client'

/**
 * jobs.js — Endpoints de vagas da API ContrataPorto
 */

/**
 * Lista vagas públicas paginadas.
 * @param {number} page
 * @param {number} limit
 */
export const list = (page = 1, limit = 6) =>
  client.get('/jobs', { params: { page, limit } })

/**
 * Filtra vagas com parâmetros opcionais.
 * @param {Object} filters - { q, area, nivel, tipo, modalidade, experiencia, salario_min, cidade, empresa_id }
 * @param {number} page
 * @param {number} limit
 */
export const filter = (filters = {}, page = 1, limit = 6) =>
  client.get('/jobs/filter', { params: { ...filters, page, limit } })

/**
 * Retorna os detalhes de uma vaga pelo ID.
 * @param {number|string} id
 */
export const show = (id) => client.get(`/jobs/${id}`)

/**
 * Lista as vagas da empresa autenticada.
 * @param {number} page
 * @param {number} limit
 */
export const myCompanyJobs = (page = 1, limit = 20) =>
  client.get('/jobs/my-company', { params: { page, limit } })

/**
 * Cria uma nova vaga para a empresa autenticada.
 * @param {Object} data
 */
export const create = (data) => client.post('/jobs', data)

/**
 * Atualiza uma vaga existente.
 * @param {number|string} id
 * @param {Object} data
 */
export const update = (id, data) => client.put(`/jobs/${id}`, data)

/**
 * Remove uma vaga.
 * @param {number|string} id
 */
export const remove = (id) => client.delete(`/jobs/${id}`)

/**
 * Alterna o status ativo/pausado de uma vaga.
 * @param {number|string} id
 */
export const toggleStatus = (id) => client.put(`/jobs/${id}/status`)

/**
 * Conclui uma vaga (ficará visível por mais 3 dias).
 * @param {number|string} id
 */
export const conclude = (id) => client.put(`/jobs/${id}/conclude`)
