import { Link, NavLink, useNavigate } from 'react-router-dom'
import { LogOut, User, Building2, Heart, Menu, X, ChevronDown, LayoutDashboard, Settings } from 'lucide-react'
import { useState } from 'react'
import { useAuth } from '@/contexts/AuthContext'
import { getInitials } from '@/utils/formatters'
import clsx from 'clsx'

export default function Header() {
  const { isAuthenticated, user, userType, logout } = useAuth()
  const [mobileOpen, setMobileOpen] = useState(false)
  const [userMenuOpen, setUserMenuOpen] = useState(false)
  const navigate = useNavigate()

  const isEmpresa   = userType === 'EMPRESA'
  const isCandidate = userType === 'CANDIDATO'

  const navLinks = [
    { to: '/vagas', label: 'Vagas' },
    ...(isCandidate ? [{ to: '/favoritos', label: 'Favoritos' }] : []),
    ...(isCandidate ? [{ to: '/dashboard/candidato', label: 'Candidaturas' }] : []),
    ...(isEmpresa   ? [{ to: '/dashboard/empresa', label: 'Minhas Vagas' }] : []),
  ]

  const handleLogout = async () => {
    setUserMenuOpen(false)
    await logout()
  }

  return (
    <header className="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-surface-200 shadow-sm">
      <div className="page-wrapper">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <Link to="/" className="flex items-center gap-2.5 no-underline group">
            <img
              src="/favicon.png"
              alt=""
              className="w-9 h-9 rounded-full object-cover flex-shrink-0 ring-1 ring-surface-200"
            />
            <img
              src="/logo.png"
              alt="ContrataPorto"
              className="h-9 w-auto object-contain hidden sm:block"
            />
          </Link>

          {/* Nav desktop */}
          <nav className="hidden md:flex items-center gap-1">
            {navLinks.map(({ to, label }) => (
              <NavLink
                key={to}
                to={to}
                className={({ isActive }) =>
                  clsx(
                    'px-3 py-2 rounded-lg text-sm font-medium transition-colors no-underline',
                    isActive
                      ? 'bg-primary-50 text-primary-700'
                      : 'text-slate-600 hover:bg-surface-100 hover:text-slate-900'
                  )
                }
              >
                {label}
              </NavLink>
            ))}
          </nav>

          {/* Ações direita */}
          <div className="flex items-center gap-2">
            {!isAuthenticated ? (
              <>
                <Link to="/login" className="btn-ghost btn-sm hidden sm:inline-flex no-underline">
                  Entrar
                </Link>
                <Link to="/cadastro" className="btn-primary btn-sm no-underline">
                  Cadastrar
                </Link>
              </>
            ) : (
              <div className="relative">
                <button
                  id="user-menu-btn"
                  onClick={() => setUserMenuOpen((o) => !o)}
                  className="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-surface-100 transition-colors"
                  aria-haspopup="true"
                  aria-expanded={userMenuOpen}
                >
                  {/* Avatar */}
                  <div className="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {getInitials(user?.nome ?? 'U')}
                  </div>
                  <span className="text-sm font-medium text-slate-700 hidden md:block max-w-[120px] truncate">
                    {user?.nome?.split(' ')[0]}
                  </span>
                  <ChevronDown className={clsx('w-4 h-4 text-slate-400 transition-transform', userMenuOpen && 'rotate-180')} />
                </button>

                {/* Dropdown */}
                {userMenuOpen && (
                  <>
                    <div
                      className="fixed inset-0 z-10"
                      onClick={() => setUserMenuOpen(false)}
                      aria-hidden
                    />
                    <div className="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-modal border border-surface-200 z-20 animate-slide-up py-1.5">
                      <div className="px-4 py-2 border-b border-surface-100 mb-1">
                        <p className="text-xs font-semibold text-slate-800 truncate">{user?.nome}</p>
                        <p className="text-xs text-slate-400 truncate">{user?.email}</p>
                      </div>

                      {isCandidate && (
                        <>
                          <DropdownItem icon={<LayoutDashboard className="w-4 h-4" />} to="/dashboard/candidato" onClick={() => setUserMenuOpen(false)}>
                            Dashboard
                          </DropdownItem>
                          <DropdownItem icon={<Settings className="w-4 h-4" />} to="/settings/candidato" onClick={() => setUserMenuOpen(false)}>
                            Configurações
                          </DropdownItem>
                          <DropdownItem icon={<Heart className="w-4 h-4" />} to="/favoritos" onClick={() => setUserMenuOpen(false)}>
                            Favoritos
                          </DropdownItem>
                        </>
                      )}
                      {isEmpresa && (
                        <>
                          <DropdownItem icon={<LayoutDashboard className="w-4 h-4" />} to="/dashboard/empresa" onClick={() => setUserMenuOpen(false)}>
                            Dashboard
                          </DropdownItem>
                          <DropdownItem icon={<Settings className="w-4 h-4" />} to="/settings/empresa" onClick={() => setUserMenuOpen(false)}>
                            Configurações
                          </DropdownItem>
                        </>
                      )}

                      <div className="border-t border-surface-100 mt-1 pt-1">
                        <button
                          onClick={handleLogout}
                          className="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-danger-500 hover:bg-danger-50 transition-colors"
                        >
                          <LogOut className="w-4 h-4" />
                          Sair
                        </button>
                      </div>
                    </div>
                  </>
                )}
              </div>
            )}

            {/* Hamburger mobile */}
            <button
              className="md:hidden p-2 rounded-lg text-slate-600 hover:bg-surface-100 transition-colors"
              onClick={() => setMobileOpen((o) => !o)}
              aria-label={mobileOpen ? 'Fechar menu' : 'Abrir menu'}
            >
              {mobileOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </button>
          </div>
        </div>
      </div>

      {/* Menu mobile */}
      {mobileOpen && (
        <div className="md:hidden bg-white border-t border-surface-200 px-4 pb-4 animate-fade-in">
          <nav className="flex flex-col gap-1 pt-3">
            {navLinks.map(({ to, label }) => (
              <NavLink
                key={to}
                to={to}
                onClick={() => setMobileOpen(false)}
                className={({ isActive }) =>
                  clsx(
                    'px-3 py-2.5 rounded-xl text-sm font-medium transition-colors no-underline',
                    isActive
                      ? 'bg-primary-50 text-primary-700'
                      : 'text-slate-700 hover:bg-surface-100'
                  )
                }
              >
                {label}
              </NavLink>
            ))}
            {!isAuthenticated && (
              <Link
                to="/login"
                onClick={() => setMobileOpen(false)}
                className="btn-outline btn-md mt-2 no-underline"
              >
                Entrar
              </Link>
            )}
          </nav>
        </div>
      )}
    </header>
  )
}

function DropdownItem({ to, icon, onClick, children }) {
  return (
    <Link
      to={to}
      onClick={onClick}
      className="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-surface-100 transition-colors no-underline"
    >
      <span className="text-slate-400">{icon}</span>
      {children}
    </Link>
  )
}
