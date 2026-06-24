import { useEffect } from 'react'
import { createPortal } from 'react-dom'
import { X } from 'lucide-react'
import clsx from 'clsx'

/**
 * Modal — janela modal acessível com portal e trap de foco.
 */
export default function Modal({
  isOpen,
  onClose,
  title,
  children,
  size = 'md',
  closeOnOverlay = true,
  className,
}) {
  useEffect(() => {
    if (!isOpen) return
    const handler = (e) => { if (e.key === 'Escape') onClose() }
    window.addEventListener('keydown', handler)
    return () => window.removeEventListener('keydown', handler)
  }, [isOpen, onClose])

  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
    return () => { document.body.style.overflow = '' }
  }, [isOpen])

  if (!isOpen) return null

  const maxW = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-2xl',
  }[size] ?? 'max-w-md'

  return createPortal(
    <div
      className="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto animate-fade-in"
      aria-modal="true"
      role="dialog"
      aria-labelledby="modal-title"
    >
      <div
        className="absolute inset-0 bg-black/50 backdrop-blur-sm"
        onClick={closeOnOverlay ? onClose : undefined}
        aria-hidden="true"
      />

      <div
        className={clsx(
          'relative w-full bg-white shadow-modal z-10 animate-slide-up flex flex-col',
          'max-h-[100dvh] sm:max-h-[calc(100dvh-2rem)]',
          'rounded-t-2xl sm:rounded-2xl',
          maxW,
          className
        )}
      >
        {title && (
          <div className="flex items-center justify-between px-4 sm:px-6 pt-5 sm:pt-6 pb-4 border-b border-surface-200 flex-shrink-0">
            <h2 id="modal-title" className="text-base sm:text-lg font-semibold text-primary-600 pr-4">
              {title}
            </h2>
            <button
              onClick={onClose}
              className="tap-target p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-surface-100 transition-colors flex-shrink-0"
              aria-label="Fechar modal"
            >
              <X className="w-5 h-5" />
            </button>
          </div>
        )}

        <div className={clsx(
          'px-4 sm:px-6 py-4 sm:py-5 overflow-y-auto flex-1',
          !title && 'pt-6'
        )}>
          {children}
        </div>
      </div>
    </div>,
    document.body
  )
}
