import { useEffect } from 'react'

const SITE_NAME = 'ContrataPorto'

/**
 * Define o título da aba do navegador para a página atual.
 */
export default function useDocumentTitle(title) {
  useEffect(() => {
    const previous = document.title
    document.title = title ? `${title} — ${SITE_NAME}` : `${SITE_NAME} — Vagas em Porto Ferreira, SP`
    return () => {
      document.title = previous
    }
  }, [title])
}
