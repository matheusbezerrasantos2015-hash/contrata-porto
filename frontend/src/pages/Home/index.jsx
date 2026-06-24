import { Link, useNavigate } from 'react-router-dom'
import { Briefcase, Search, ArrowRight, CheckCircle2, MapPin } from 'lucide-react'
import { useEffect, useState } from 'react'
import * as jobsApi from '@/api/jobs'
import JobCard from '@/components/JobCard'
import Spinner from '@/components/Spinner'
import useDocumentTitle from '@/hooks/useDocumentTitle'

const CIDADES = [
  'Porto Ferreira',
  'Piracicaba',
  'Santa Rosa de Viterbo',
  'Santa Cruz das Palmeiras',
  'Saltinho',
  'Araras',
  'Rio Claro',
  'São Pedro',
]

export default function Home() {
  useDocumentTitle(null)

  const [featuredJobs, setFeaturedJobs] = useState([])
  const [loading, setLoading] = useState(true)
  const [searchTerm, setSearchTerm] = useState('')
  const [searchCidade, setSearchCidade] = useState('')
  const navigate = useNavigate()

  useEffect(() => {
    jobsApi
      .list(1, 6)
      .then((res) => {
        setFeaturedJobs(res.data?.data?.vagas ?? [])
      })
      .catch(() => {})
      .finally(() => setLoading(false))
  }, [])

  const handleSearchSubmit = (e) => {
    e.preventDefault()
    const params = new URLSearchParams()
    if (searchTerm) params.append('q', searchTerm)
    if (searchCidade) params.append('cidade', searchCidade)
    navigate(`/vagas?${params.toString()}`)
  }

  return (
    <div className="flex flex-col min-h-screen">
      {/* Hero Section */}
      <section className="bg-gradient-to-br from-primary-900 via-primary-800 to-primary-950 text-white py-20 relative overflow-hidden">
        <div className="absolute inset-0 opacity-10 pointer-events-none">
          <div className="absolute top-10 left-10 w-72 h-72 rounded-full bg-accent-500 blur-3xl" />
          <div className="absolute bottom-10 right-10 w-96 h-96 rounded-full bg-primary-400 blur-3xl" />
        </div>

        <div className="page-wrapper relative z-10">
          <div className="max-w-3xl mx-auto text-center">
            <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-primary-700/60 border border-primary-600 text-primary-200 mb-6 animate-fade-in">
              🚀 A plataforma de empregos oficial de Porto Ferreira, SP
            </span>
            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-white text-balance leading-tight">
              Conectando você às melhores vagas de <span className="text-accent-400">Porto Ferreira, SP</span>
            </h1>
            <p className="mt-6 text-lg text-primary-200 text-balance max-w-2xl mx-auto">
              Seja você um profissional buscando novos desafios ou uma empresa procurando o talento perfeito. Nosso porto é o seu ponto de partida.
            </p>

            {/* Search Bar com Cidade */}
            <form
              onSubmit={handleSearchSubmit}
              className="mt-10 max-w-3xl mx-auto bg-white p-2 rounded-2xl shadow-lg border border-surface-200 flex flex-col md:flex-row gap-2"
            >
              <div className="flex-1 flex items-center px-3 gap-2">
                <Search className="w-5 h-5 text-slate-400 flex-shrink-0" />
                <input
                  type="text"
                  placeholder="Cargo, palavra-chave ou empresa..."
                  className="w-full py-2.5 text-sm text-slate-800 focus:outline-none placeholder-slate-400"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                />
              </div>

              <div className="flex items-center px-3 gap-2 border-t md:border-t-0 md:border-l border-surface-200 py-2 md:py-0">
                <MapPin className="w-5 h-5 text-slate-400 flex-shrink-0" />
                <select
                  className="py-2.5 text-sm text-slate-700 bg-transparent focus:outline-none pr-8 cursor-pointer"
                  value={searchCidade}
                  onChange={(e) => setSearchCidade(e.target.value)}
                >
                  <option value="">Todas as cidades</option>
                  {CIDADES.map((c) => (
                    <option key={c} value={c} className="text-slate-800">{c}</option>
                  ))}
                </select>
              </div>

              <button type="submit" className="btn-accent btn-md sm:btn-lg rounded-xl whitespace-nowrap">
                Buscar vagas
              </button>
            </form>
          </div>
        </div>
      </section>

      {/* Featured Jobs */}
      <section className="py-20 bg-surface-50">
        <div className="page-wrapper">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-10 gap-4">
            <div>
              <h2 className="text-2xl sm:text-3xl font-bold text-primary-600">Vagas em Destaque</h2>
              <p className="text-slate-500 text-sm mt-1">As oportunidades mais recentes publicadas na nossa plataforma.</p>
            </div>
            <Link to="/vagas" className="inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700 no-underline group">
              Ver todas as vagas
              <ArrowRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
            </Link>
          </div>

          {loading ? (
            <Spinner size="lg" />
          ) : featuredJobs.length === 0 ? (
            <div className="card p-12 text-center bg-white max-w-xl mx-auto">
              <Briefcase className="w-12 h-12 text-slate-300 mx-auto mb-4" />
              <p className="text-slate-600 font-medium">Nenhuma vaga encontrada no momento.</p>
              <p className="text-slate-400 text-sm mt-1">Que tal cadastrar sua empresa e publicar a primeira vaga?</p>
              <Link to="/cadastro" className="btn-primary btn-md mt-6 no-underline">
                Publicar Vaga
              </Link>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              {featuredJobs.map((job) => (
                <JobCard key={job.id} job={job} />
              ))}
            </div>
          )}
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 bg-white border-t border-surface-200">
        <div className="page-wrapper">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
              <h2 className="text-3xl font-bold text-primary-600 leading-tight">
                Por que escolher o ContrataPorto?
              </h2>
              <p className="text-slate-600 mt-4 leading-relaxed">
                Nós facilitamos a conexão entre talentos locais e empresas inovadoras em Porto Ferreira, SP e região, focando na agilidade e transparência do processo de contratação.
              </p>

              <ul className="mt-8 space-y-3">
                <li className="flex items-start gap-2.5">
                  <CheckCircle2 className="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" />
                  <span className="text-slate-700 text-sm font-medium">Cadastro simples e gratuito para candidatos.</span>
                </li>
                <li className="flex items-start gap-2.5">
                  <CheckCircle2 className="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" />
                  <span className="text-slate-700 text-sm font-medium">Filtros avançados de região, salário e modalidade.</span>
                </li>
                <li className="flex items-start gap-2.5">
                  <CheckCircle2 className="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" />
                  <span className="text-slate-700 text-sm font-medium">Painel completo para empresas gerenciarem processos.</span>
                </li>
              </ul>
            </div>

            <div className="bg-gradient-to-tr from-primary-50 to-primary-100/50 p-8 rounded-2xl border border-primary-100 flex flex-col justify-between h-full gap-8">
              <div>
                <h3 className="text-xl font-bold text-primary-700">Sua empresa busca novos talentos?</h3>
                <p className="text-slate-600 text-sm mt-2 leading-relaxed">
                  Publique suas vagas gratuitamente, receba currículos formatados em PDF e filtre candidatos no nosso painel de gerenciamento corporativo.
                </p>
              </div>

              <div className="flex flex-col sm:flex-row gap-3">
                <Link to="/cadastro?role=EMPRESA" className="btn-primary btn-md flex-1 text-center no-underline">
                  Cadastrar Empresa
                </Link>
                <Link to="/login" className="btn-outline btn-md flex-1 text-center no-underline">
                  Acessar Painel
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
