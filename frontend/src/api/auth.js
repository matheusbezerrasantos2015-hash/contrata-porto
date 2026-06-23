import client from './client'

/**
 * auth.js — Endpoints de autenticação da API ContrataPorto
 */

/**
 * Registra um novo usuário (candidato ou empresa).
 * @param {Object} data - { nome, email, senha, role, company? }
 */
export const register = (data) => client.post('/auth/register', data)

/**
 * Realiza o login e retorna token + dados do usuário.
 * @param {string} email
 * @param {string} senha
 */
export const login = (email, senha) => client.post('/auth/login', { email, senha })

/**
 * Encerra a sessão do usuário autenticado.
 */
export const logout = () => client.post('/auth/logout')

/**
 * Solicita o envio de link de recuperação de senha.
 * @param {string} email
 */
export const forgotPassword = (email) => client.post('/auth/recover', { email })

/**
 * Redefine a senha com base em um token de reset.
 * @param {string} token
 * @param {string} nova_senha
 */
export const resetPassword = (token, nova_senha) =>
  client.post('/auth/reset', { token, nova_senha })

/**
 * Verifica o e-mail do usuário com o código de 6 dígitos.
 * @param {string} email
 * @param {string} code
 */
export const verifyEmail = (email, code) =>
  client.post('/auth/verify-email', { email, code })

/**
 * Reenvia o código de verificação de e-mail.
 * @param {string} email
 */
export const resendVerification = (email) =>
  client.post('/auth/resend-verification', { email })
