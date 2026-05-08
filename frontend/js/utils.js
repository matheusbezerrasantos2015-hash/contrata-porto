/**
 * utils.js — Utilitários de segurança e helpers de DOM
 * Centraliza a sanitização de dados para prevenir ataques XSS.
 */

/**
 * Escapa uma string para uso seguro dentro de HTML.
 * Converte caracteres especiais (<, >, &, ", ') em entidades HTML.
 * @param {*} str - Valor a ser escapado (será convertido para string).
 * @returns {string} String segura para inserção em innerHTML.
 */
export function escapeHTML(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

/**
 * Cria um elemento e define seu textContent de forma segura.
 * @param {string} tag - Tag HTML do elemento.
 * @param {string} text - Texto a ser inserido com segurança.
 * @param {string} [className] - Classe CSS opcional.
 * @returns {HTMLElement}
 */
export function createTextElement(tag, text, className) {
  const el = document.createElement(tag);
  el.textContent = text ?? '';
  if (className) el.className = className;
  return el;
}

/**
 * Define o textContent de um elemento de forma segura, somente se ele existir.
 * @param {HTMLElement|null} el
 * @param {*} value
 */
export function safeSetText(el, value) {
  if (!el) return;
  el.textContent = value ?? '';
}
