import clsx from 'clsx'

/**
 * Badge — tag visual de status ou categoria.
 *
 * @prop {'primary'|'accent'|'success'|'danger'|'warning'|'neutral'} variant
 * @prop {React.ReactNode} icon - ícone opcional à esquerda do texto
 */
export default function Badge({ children, variant = 'primary', icon, className }) {
  return (
    <span
      className={clsx(
        `badge-${variant}`,
        className
      )}
    >
      {icon && <span className="flex-shrink-0">{icon}</span>}
      {children}
    </span>
  )
}
