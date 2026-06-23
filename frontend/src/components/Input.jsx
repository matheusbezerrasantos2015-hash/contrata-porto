import { forwardRef } from 'react'
import clsx from 'clsx'

/**
 * Input — campo de formulário estilizado.
 *
 * @prop {string} label - texto do rótulo
 * @prop {string} error - mensagem de erro (exibida abaixo do input)
 * @prop {string} hint - dica auxiliar (exibida abaixo do input, antes do error)
 * @prop {React.ReactNode} leftIcon - ícone à esquerda
 * @prop {React.ReactNode} rightIcon - ícone à direita
 * @prop {boolean} required - marca o campo como obrigatório visualmente
 */
const Input = forwardRef(function Input(
  {
    label,
    error,
    hint,
    leftIcon,
    rightIcon,
    required,
    id,
    className,
    wrapperClassName,
    ...props
  },
  ref
) {
  const inputId = id ?? label?.toLowerCase().replace(/\s+/g, '-')

  return (
    <div className={clsx('w-full', wrapperClassName)}>
      {label && (
        <label htmlFor={inputId} className="label">
          {label}
          {required && <span className="text-danger-500 ml-0.5">*</span>}
        </label>
      )}

      <div className="relative">
        {leftIcon && (
          <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
            {leftIcon}
          </span>
        )}

        <input
          ref={ref}
          id={inputId}
          className={clsx(
            'input',
            error && 'input-error',
            leftIcon  && 'pl-10',
            rightIcon && 'pr-10',
            className
          )}
          aria-describedby={
            error ? `${inputId}-error` : hint ? `${inputId}-hint` : undefined
          }
          aria-invalid={!!error}
          {...props}
        />

        {rightIcon && (
          <span className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
            {rightIcon}
          </span>
        )}
      </div>

      {hint && !error && (
        <p id={`${inputId}-hint`} className="mt-1 text-xs text-slate-500">
          {hint}
        </p>
      )}
      {error && (
        <p id={`${inputId}-error`} role="alert" className="mt-1 text-xs text-danger-500">
          {error}
        </p>
      )}
    </div>
  )
})

export default Input
