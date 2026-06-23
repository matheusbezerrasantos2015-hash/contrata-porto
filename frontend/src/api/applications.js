import client from './client'

/**
 * applications.js — Endpoints de candidaturas da API ContrataPorto
 */

/**
 * Envia uma candidatura para uma vaga.
 * Suporta envio de currículo (multipart/form-data) ou JSON puro.
 *
 * @param {Object} data - { vaga_id|job_id, mensagem?, linkedin?, portfolio?, telefone? }
 * @param {File|null} curriculoFile - arquivo PDF opcional
 */
export const apply = (data, curriculoFile = null) => {
  if (curriculoFile) {
    const formData = new FormData()
    Object.entries(data).forEach(([key, val]) => {
      if (val !== undefined && val !== null) formData.append(key, val)
    })
    formData.append('curriculo', curriculoFile)
    return client.post('/applications', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  }
  return client.post('/applications', data)
}

/**
 * Lista as candidaturas do candidato autenticado.
 * @param {number} page
 * @param {number} limit
 */
export const myApplications = (page = 1, limit = 10) =>
  client.get('/applications/me', { params: { page, limit } })

/**
 * Retorna os detalhes de uma candidatura (para a empresa).
 * @param {number|string} id
 */
export const show = (id) => client.get(`/applications/${id}`)

/**
 * Lista candidaturas recebidas para uma vaga específica (para a empresa).
 * @param {number|string} jobId
 * @param {number} page
 * @param {number} limit
 */
export const jobApplications = (jobId, page = 1, limit = 10) =>
  client.get(`/jobs/${jobId}/applications`, { params: { page, limit } })

/**
 * Atualiza o status de uma candidatura (para a empresa).
 * @param {number|string} id
 * @param {string} status - 'pendente' | 'em_analise' | 'aprovado' | 'recusado'
 */
export const updateStatus = (id, status) =>
  client.put(`/applications/${id}`, { status })

/**
 * Retorna a URL do currículo de uma candidatura (redireciona).
 * @param {number|string} id
 */
export const downloadCurriculo = (id) =>
  client.get(`/applications/${id}/curriculo`, { maxRedirects: 0 })
