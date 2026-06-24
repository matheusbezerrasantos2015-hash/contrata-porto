import { useFavorites } from '@/hooks/useFavorites'
import { Heart, Search } from 'lucide-react'
import { Link } from 'react-router-dom'
import JobCard from '@/components/JobCard'
import useDocumentTitle from '@/hooks/useDocumentTitle'

export default function Favorites() {
  useDocumentTitle('Favoritos')
  const { favorites, loading } = useFavorites()

  return (
    <div className="page-wrapper py-10">
      <div className="mb-8">
        <h1 className="text-2xl sm:text-3xl font-bold text-primary-600">Vagas Favoritas</h1>
        <p className="text-slate-500 text-sm mt-1">Acompanhe as oportunidades que você marcou como favoritas.</p>
      </div>

      {loading ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {[1, 2, 3].map((n) => (
            <div key={n} className="card p-6 h-56 animate-pulse bg-white flex flex-col gap-4">
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 bg-slate-200 rounded-xl" />
                <div className="flex-1 space-y-2">
                  <div className="h-4 bg-slate-200 rounded w-2/3" />
                  <div className="h-3 bg-slate-200 rounded w-1/2" />
                </div>
              </div>
              <div className="h-3 bg-slate-200 rounded w-3/4 mt-auto" />
            </div>
          ))}
        </div>
      ) : favorites.length === 0 ? (
        <div className="card p-6 sm:p-12 text-center bg-white max-w-xl mx-auto w-full">
          <Heart className="w-12 h-12 text-slate-300 mx-auto mb-4" />
          <p className="text-slate-600 font-semibold">Nenhuma vaga favorita.</p>
          <p className="text-slate-400 text-sm mt-1">Explore a busca de vagas e clique no ícone de coração para salvar aqui.</p>
          <Link to="/vagas" className="btn-primary btn-md mt-6 no-underline inline-flex items-center gap-1.5 rounded-xl">
            <Search className="w-4 h-4" />
            Buscar Vagas
          </Link>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {favorites.map((job) => (
            <JobCard key={job.id} job={job} />
          ))}
        </div>
      )}
    </div>
  )
}
