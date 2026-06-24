import { Link } from 'react-router-dom'
import { Linkedin, Mail } from 'lucide-react'

export default function Footer() {
  const year = new Date().getFullYear()

  return (
    <footer className="bg-primary-900 text-white mt-auto">
      <div className="page-wrapper py-12">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          {/* Brand */}
          <div className="lg:col-span-2">
            <div className="mb-4">
              <img
                src="/logo.png"
                alt="ContrataPorto — Vagas & Oportunidades em Porto Ferreira - SP"
                className="h-14 w-auto object-contain"
              />
            </div>
            <p className="text-primary-200 text-sm leading-relaxed max-w-xs">
              Conectando talentos às melhores oportunidades em Porto Ferreira, SP e região.
              Encontre sua próxima vaga ou contrate os melhores profissionais.
            </p>
          </div>

          {/* Links */}
          <div>
            <h3 className="font-semibold text-sm mb-4 text-white">Candidatos</h3>
            <ul className="space-y-2 text-sm text-primary-300">
              <li><Link to="/vagas" className="hover:text-white transition-colors no-underline">Buscar Vagas</Link></li>
              <li><Link to="/cadastro" className="hover:text-white transition-colors no-underline">Criar Conta</Link></li>
              <li><Link to="/favoritos" className="hover:text-white transition-colors no-underline">Favoritos</Link></li>
              <li><Link to="/dashboard/candidato" className="hover:text-white transition-colors no-underline">Minhas Candidaturas</Link></li>
            </ul>
          </div>

          <div>
            <h3 className="font-semibold text-sm mb-4 text-white">Empresas</h3>
            <ul className="space-y-2 text-sm text-primary-300">
              <li><Link to="/dashboard/empresa" className="hover:text-white transition-colors no-underline">Publicar Vagas</Link></li>
              <li><Link to="/settings/empresa" className="hover:text-white transition-colors no-underline">Perfil da Empresa</Link></li>
            </ul>
          </div>
        </div>

        <div className="border-t border-primary-800 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p className="text-primary-400 text-xs">
            © {year} ContrataPorto. Todos os direitos reservados.
          </p>
          <div className="flex items-center gap-3">
            <a
              href="mailto:contato@contrataporto.com.br"
              aria-label="E-mail"
              className="p-2 rounded-lg text-primary-400 hover:text-white hover:bg-primary-800 transition-colors"
            >
              <Mail className="w-4 h-4" />
            </a>
            <a
              href="https://linkedin.com"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="LinkedIn"
              className="p-2 rounded-lg text-primary-400 hover:text-white hover:bg-primary-800 transition-colors"
            >
              <Linkedin className="w-4 h-4" />
            </a>
          </div>
        </div>
      </div>
    </footer>
  )
}
