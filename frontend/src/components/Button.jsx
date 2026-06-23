import clsx from 'clsx'
import { Loader2 } from 'lucide-react'

/**
 * Button — componente de botão reutilizável.
 *
 * @prop {'primary'|'accent'|'outline'|'ghost'|'danger'} variant
 * @prop {'sm'|'md'|'lg'} size
 * @prop {boolean} loading - exibe spinner e desabilita o botão
 * @prop {boolean} fullWidth - ocupar 100% da largura
 * @prop {React.ReactNode} leftIcon
 * @prop {React.ReactNode} rightIcon
 */
export default function Button({
  children,
  variant = 'primary',
  size = 'md',
  loading = false,
  fullWidth = false,
  leftIcon,
  rightIcon,
  className,
  disabled,
  type = 'button',
  ...props
}) {
  const base = clsx(
    'btn',
    {
      'btn-sm':      size === 'sm',
      'btn-md':      size === 'md',
      'btn-lg':      size === 'lg',
      'btn-primary': variant === 'primary',
      'btn-accent':  variant === 'accent',
      'btn-outline': variant === 'outline',
      'btn-ghost':   variant === 'ghost',
      'btn-danger':  variant === 'danger',
      'w-full':      fullWidth,
    },
    className
  )

  return (
    <button type={type} className={base} disabled={disabled || loading} {...props}>
      {loading ? (
        <Loader2 className="w-4 h-4 animate-spin" aria-hidden />
      ) : (
        leftIcon && <span className="flex-shrink-0">{leftIcon}</span>
      )}
      {children}
      {!loading && rightIcon && (
        <span className="flex-shrink-0">{rightIcon}</span>
      )}
    </button>
  )
}
