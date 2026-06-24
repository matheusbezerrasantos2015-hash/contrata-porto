import { AlertCircle } from 'lucide-react'

/**
 * EmptyState — Ilustração e mensagem para listas vazias.
 *
 * @prop {React.ReactNode} icon - ícone ou imagem
 * @prop {string} title
 * @prop {string} description
 * @prop {React.ReactNode} action - botão ou link para ação
 */
export default function EmptyState({
  icon = <AlertCircle className="w-12 h-12 text-slate-300" />,
  title = 'Nenhum resultado encontrado',
  description = 'Não há itens para mostrar no momento.',
  action,
}) {
  return (
    <div className="card p-6 sm:p-12 text-center bg-white max-w-xl mx-auto w-full flex flex-col items-center justify-center border border-surface-200">
      <div className="mb-4 text-slate-300">
        {icon}
      </div>
      <h3 className="text-base font-bold text-slate-800 leading-snug">
        {title}
      </h3>
      <p className="text-slate-400 text-sm mt-1 max-w-sm mx-auto">
        {description}
      </p>
      {action && <div className="mt-6">{action}</div>}
    </div>
  )
}
