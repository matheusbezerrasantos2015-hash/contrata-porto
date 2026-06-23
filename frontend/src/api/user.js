import client from './client'

/**
 * user.js — Endpoints do perfil de candidato
 */

/**
 * Retorna os dados do usuário autenticado.
 */
export const me = () => client.get('/me')

/**
 * Atualiza o perfil do candidato autenticado.
 * Aceita FormData (quando há arquivo de avatar) ou Object simples.
 * Quando FormData, o browser define automaticamente o Content-Type
 * com boundary correto — por isso removemos o header padrão.
 *
 * @param {FormData|Object} data
 */
export const updateProfile = (data) => {
  const isFormData = data instanceof FormData
  return client.put('/profile', data, {
    headers: isFormData ? { 'Content-Type': undefined } : {},
  })
}

/**
 * Exclui a conta do candidato autenticado.
 */
export const deleteAccount = () => client.delete('/profile')
