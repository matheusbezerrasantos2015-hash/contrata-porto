import { Loader2 } from 'lucide-react'
import clsx from 'clsx'

/**
 * Spinner — Loading spinner centralizado para tela ou componentes.
 *
 * @prop {'sm'|'md'|'lg'} size
 * @prop {boolean} fullPage - centraliza no meio da tela com min-h screen
 */
export default function Spinner({ size = 'md', fullPage = false }) {
  const spinnerSize = {
    sm: 'w-6 h-6',
    md: 'w-10 h-10',
    lg: 'w-16 h-16',
  }[size] ?? 'w-10 h-10'

  return (
    <div
      className={clsx(
        'flex items-center justify-center',
        fullPage ? 'min-h-[70vh] w-full' : 'p-8 w-full'
      )}
    >
      <Loader2 className={clsx('text-primary-600 animate-spin', spinnerSize)} />
    </div>
  )
}
