import { Routes, Route, Navigate } from 'react-router-dom'
import ProtectedRoute from './ProtectedRoute'

// Páginas Públicas
import Home from '@/pages/Home'
import Login from '@/pages/Auth/Login'
import Cadastro from '@/pages/Auth/Cadastro'
import VerificarEmail from '@/pages/Auth/VerificarEmail'
import EsqueciSenha from '@/pages/Auth/EsqueciSenha'
import ResetSenha from '@/pages/Auth/ResetSenha'
import Jobs from '@/pages/Jobs'
import JobDetail from '@/pages/JobDetail'

// Páginas de Suporte / Legadas Reutilizadas
import Favorites from '@/pages/Favorites'
import JobApplicationsList from '@/pages/JobApplicationsList'

// Dashboards
import CandidatoDashboard from '@/pages/Dashboard/CandidatoDashboard'
import EmpresaDashboard from '@/pages/Dashboard/EmpresaDashboard'

// Configurações (Settings)
import CandidatoSettings from '@/pages/Settings/CandidatoSettings'
import EmpresaSettings from '@/pages/Settings/EmpresaSettings'

export default function AppRoutes() {
  return (
    <Routes>
      {/* ── Rotas Públicas ────────────────────────────────────────────────── */}
      <Route path="/" element={<Home />} />
      <Route path="/login" element={<Login />} />
      <Route path="/cadastro" element={<Cadastro />} />
      <Route path="/verificar-email" element={<VerificarEmail />} />
      <Route path="/esqueci-senha" element={<EsqueciSenha />} />
      <Route path="/reset-senha" element={<ResetSenha />} />
      <Route path="/vagas" element={<Jobs />} />
      <Route path="/vagas/:id" element={<JobDetail />} />

      {/* ── Rotas Protegidas (Candidato) ─────────────────────────────────── */}
      <Route element={<ProtectedRoute role="CANDIDATO" />}>
        <Route path="/dashboard/candidato" element={<CandidatoDashboard />} />
        <Route path="/settings/candidato" element={<CandidatoSettings />} />
        <Route path="/favoritos" element={<Favorites />} />
      </Route>

      {/* ── Rotas Protegidas (Empresa) ───────────────────────────────────── */}
      <Route element={<ProtectedRoute role="EMPRESA" />}>
        <Route path="/dashboard/empresa" element={<EmpresaDashboard />} />
        <Route path="/settings/empresa" element={<EmpresaSettings />} />
        <Route path="/empresa/vagas/:id/candidaturas" element={<JobApplicationsList />} />
      </Route>

      {/* Redirecionamento de rotas inexistentes */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  )
}
