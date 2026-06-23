import { forwardRef } from 'react'
import clsx from 'clsx'

/**
 * Select — Campo select integrado ao React Hook Form.
 *
 * @prop {string} label
 * @prop {string} error
 * @prop {string} hint
 * @prop {boolean} required
 * @prop {Array<{value: string|number, label: string}>} options - opcional se passar children
 */
const Select = forwardRef(function Select(
  {
    label,
    error,
    hint,
    required,
    id,
    options,
    children,
    className,
    wrapperClassName,
    ...props
  },
  ref
) {
  const selectId = id ?? label?.toLowerCase().replace(/\s+/g, '-')

  return (
    <div className={clsx('w-full', wrapperClassName)}>
      {label && (
        <label htmlFor={selectId} className="label">
          {label}
          {required && <span className="text-danger-500 ml-0.5">*</span>}
        </label>
      )}

      <div className="relative">
        <select
          ref={ref}
          id={selectId}
          className={clsx(
            'input appearance-none pr-10 bg-[url("data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%20%22%20fill%3D%22none%22%3E%3Cpath%20d%3D%22M7%209l3%203%203-3%22%20stroke%3D%22%252364748b%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%3C%2Fsvg%3E")] bg-[position:right_0.5rem_center] bg-[size:1.5em_1.5em] bg-no-repeat',
            error && 'input-error',
            className
          )}
          aria-describedby={
            error ? `${selectId}-error` : hint ? `${selectId}-hint` : undefined
          }
          aria-invalid={!!error}
          {...props}
        >
          {children ? (
            children
          ) : (
            <>
              <option value="">Selecione uma opção</option>
              {options?.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </>
          )}
        </select>
      </div>

      {hint && !error && (
        <p id={`${selectId}-hint`} className="mt-1 text-xs text-slate-500">
          {hint}
        </p>
      )}
      {error && (
        <p id={`${selectId}-error`} role="alert" className="mt-1 text-xs text-danger-500">
          {error}
        </p>
      )}
    </div>
  )
})

export default Select
