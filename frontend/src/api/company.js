import client from './client'

/**
 * company.js — Endpoints do perfil de empresa
 */

/**
 * Retorna o perfil da empresa do usuário autenticado.
 */
export const getProfile = () => client.get('/empresa/profile')

/**
 * Atualiza o perfil da empresa autenticada.
 * Aceita FormData (quando há arquivo de logo) ou Object simples.
 *
 * @param {FormData|Object} data
 */
export const updateProfile = (data) => {
  const isFormData = data instanceof FormData
  return client.put('/empresa/profile', data, {
    headers: isFormData ? { 'Content-Type': undefined } : {},
  })
}

/**
 * Exclui a conta da empresa autenticada.
 */
export const deleteAccount = () => client.delete('/empresa/profile')

/**
 * Retorna dados públicos de uma empresa pelo ID.
 * @param {number|string} id
 */
export const show = (id) => client.get(`/empresas/${id}`)

/**
 * Cria uma empresa para o usuário autenticado do tipo EMPRESA.
 * @param {Object} data - { nome_fantasia, cnpj, descricao?, site? }
 */
export const create = (data) => client.post('/empresas', data)
