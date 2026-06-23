import { useJobs } from '@/hooks/useJobs'
import { useSearchParams } from 'react-router-dom'
import { useEffect, useState } from 'react'
import { useForm } from 'react-hook-form'
import { Search, MapPin, Filter, ChevronLeft, ChevronRight, SlidersHorizontal, X } from 'lucide-react'
import JobCard from '@/components/JobCard'
import Input from '@/components/Input'
import Select from '@/components/Select'
import Button from '@/components/Button'
import EmptyState from '@/components/EmptyState'

const AREAS = [
  'Tecnologia da Informação',
  'Administração e Finanças',
  'Marketing e Vendas',
  'Recursos Humanos',
  'Atendimento e Suporte',
  'Design e Criação',
  'Logística e Operações',
  'Saúde e Bem-estar',
  'Educação',
  'Outros',
]

const NIVEIS = [
  { value: 'Estágio', label: 'Estágio' },
  { value: 'Júnior', label: 'Júnior' },
  { value: 'Pleno', label: 'Pleno' },
  { value: 'Sênior', label: 'Sênior' },
  { value: 'Especialista', label: 'Especialista' },
  { value: 'Gerência', label: 'Gerência' },
]

const TIPOS = [
  { value: 'CLT', label: 'CLT' },
  { value: 'PJ', label: 'PJ' },
  { value: 'Freelancer', label: 'Freelancer' },
  { value: 'Estágio', label: 'Estágio' },
  { value: 'Temporário', label: 'Temporário' },
  { value: 'Jovem_Aprendiz', label: 'Jovem Aprendiz' },
]

const MODALIDADES = [
  { value: 'presencial', label: 'Presencial' },
  { value: 'remoto', label: 'Remoto' },
  { value: 'hibrido', label: 'Híbrido' },
]

export default function Jobs() {
  const [searchParams, setSearchParams] = useSearchParams()
  const [mobileFiltersOpen, setMobileFiltersOpen] = useState(false)

  // Valores iniciais dos filtros extraídos da URL
  const initialValues = {
    q: searchParams.get('q') ?? '',
    area: searchParams.get('area') ?? '',
    nivel: searchParams.get('nivel') ?? '',
    tipo: searchParams.get('tipo') ?? '',
    modalidade: searchParams.get('modalidade') ?? '',
    cidade: searchParams.get('cidade') ?? '',
    salario_min: searchParams.get('salario_min') ?? '',
  }

  const { register, watch, reset, setValue } = useForm({
    defaultValues: initialValues,
  })

  // Assiste a todos os campos para fazer filtros reativos
  const formValues = watch()

  const {
    jobs,
    total,
    page,
    lastPage,
    loading,
    setPage,
    setFilters,
  } = useJobs(initialValues)

  // Efeito reativo: quando qualquer valor de input do formulário mudar, atualizamos a URL e os filtros do hook
  useEffect(() => {
    const cleaned = {}
    Object.entries(formValues).forEach(([k, v]) => {
      if (v) cleaned[k] = v
    })
    
    // Atualiza a URL sem causar full page reload
    setSearchParams(cleaned, { replace: true })
    setFilters(cleaned)
  }, [formValues, setSearchParams, setFilters])

  // Sincroniza o form se a URL for alterada por links externos (ex: clique no Header)
  useEffect(() => {
    reset({
      q: searchParams.get('q') ?? '',
      area: searchParams.get('area') ?? '',
      nivel: searchParams.get('nivel') ?? '',
      tipo: searchParams.get('tipo') ?? '',
      modalidade: searchParams.get('modalidade') ?? '',
      cidade: searchParams.get('cidade') ?? '',
      salario_min: searchParams.get('salario_min') ?? '',
    })
  }, [searchParams, reset])

  const clearFilters = () => {
    reset({ q: '', area: '', nivel: '', tipo: '', modalidade: '', salario_min: '', cidade: '' })
    setSearchParams({})
  }

  const handlePageChange = (newPage) => {
    if (newPage >= 1 && newPage <= lastPage) {
      setPage(newPage)
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }

  return (
    <div className="page-wrapper py-10">
      {/* Header busca principal */}
      <div className="bg-primary-900 rounded-2xl p-6 sm:p-8 text-white mb-8 border border-primary-800 shadow-md">
        <h1 className="text-2xl sm:text-3xl font-bold text-white mb-2">Encontre sua oportunidade</h1>
        <p className="text-primary-200 text-sm mb-6">Busque por cargos, empresas, áreas ou cidades da região metropolitana.</p>

        <div className="grid grid-cols-1 md:grid-cols-12 gap-3 bg-white p-2 rounded-xl border border-surface-200">
          <div className="md:col-span-6 flex items-center px-3 gap-2 border-b md:border-b-0 md:border-r border-surface-200 pb-2 md:pb-0">
            <Search className="w-5 h-5 text-slate-400 flex-shrink-0" />
            <input
              type="text"
              placeholder="Cargo ou palavra-chave..."
              className="w-full py-2 text-sm text-slate-800 focus:outline-none placeholder-slate-400"
              {...register('q')}
            />
          </div>
          <div className="md:col-span-6 flex items-center px-3 gap-2 pb-2 md:pb-0">
            <MapPin className="w-5 h-5 text-slate-400 flex-shrink-0" />
            <input
              type="text"
              placeholder="Cidade ou região..."
              className="w-full py-2 text-sm text-slate-800 focus:outline-none placeholder-slate-400"
              {...register('cidade')}
            />
          </div>
        </div>
      </div>

      <div className="flex items-center justify-between mb-6">
        <p className="text-slate-500 text-sm font-medium">
          {loading ? 'Carregando vagas...' : `${total} ${total === 1 ? 'vaga encontrada' : 'vagas encontradas'}`}
        </p>
        <button
          onClick={() => setMobileFiltersOpen(true)}
          className="md:hidden inline-flex items-center gap-1.5 btn-outline btn-sm font-semibold rounded-xl"
        >
          <SlidersHorizontal className="w-4 h-4" />
          Filtros
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-8 items-start">
        {/* Sidebar Filtros (Desktop) */}
        <aside className="hidden md:block card p-6 sticky top-20 bg-white">
          <div className="flex items-center justify-between pb-4 border-b border-surface-200 mb-5">
            <h2 className="text-base font-bold text-slate-800 flex items-center gap-2">
              <Filter className="w-4 h-4 text-primary-500" />
              Filtrar Vagas
            </h2>
            <button onClick={clearFilters} className="text-xs font-semibold text-slate-400 hover:text-primary-600 transition-colors">
              Limpar Tudo
            </button>
          </div>

          <form className="space-y-5" onSubmit={(e) => e.preventDefault()}>
            <Select label="Área Profissional" {...register('area')}>
              <option value="">Todas as áreas</option>
              {AREAS.map((a) => (
                <option key={a} value={a}>{a}</option>
              ))}
            </Select>

            <Select label="Nível" options={NIVEIS} {...register('nivel')} />
            <Select label="Modalidade" options={MODALIDADES} {...register('modalidade')} />
            <Select label="Contrato" options={TIPOS} {...register('tipo')} />

            <Input
              label="Salário Mínimo (R$)"
              type="number"
              placeholder="Ex: 3000"
              className="py-2 text-xs"
              {...register('salario_min')}
            />
          </form>
        </aside>

        {/* Lista de Vagas */}
        <section className="md:col-span-3 flex flex-col gap-6">
          {loading ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
              {[1, 2, 3, 4].map((n) => (
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
          ) : jobs.length === 0 ? (
            <EmptyState
              title="Nenhuma vaga disponível"
              description="Não encontramos vagas de acordo com as seleções de filtros informadas."
              action={
                <Button onClick={clearFilters} variant="primary">
                  Limpar Filtros
                </Button>
              }
            />
          ) : (
            <>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {jobs.map((job) => (
                  <JobCard key={job.id} job={job} />
                ))}
              </div>

              {/* Paginação */}
              {lastPage > 1 && (
                <div className="flex items-center justify-center gap-2 mt-8">
                  <button
                    onClick={() => handlePageChange(page - 1)}
                    disabled={page === 1}
                    className="p-2.5 rounded-xl border border-surface-200 bg-white text-slate-600 hover:bg-surface-55 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    <ChevronLeft className="w-5 h-5" />
                  </button>
                  <span className="text-sm font-semibold text-slate-700 px-4 py-2 border border-surface-200 rounded-xl bg-white">
                    Página {page} de {lastPage}
                  </span>
                  <button
                    onClick={() => handlePageChange(page + 1)}
                    disabled={page === lastPage}
                    className="p-2.5 rounded-xl border border-surface-200 bg-white text-slate-600 hover:bg-surface-55 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    <ChevronRight className="w-5 h-5" />
                  </button>
                </div>
              )}
            </>
          )}
        </section>
      </div>

      {/* Modal Drawer de Filtros Mobile */}
      {mobileFiltersOpen && (
        <div className="fixed inset-0 z-50 flex justify-end animate-fade-in">
          <div className="absolute inset-0 bg-black/50 backdrop-blur-sm" onClick={() => setMobileFiltersOpen(false)} />
          <div className="relative w-80 max-w-full bg-white h-full shadow-modal flex flex-col z-10 animate-slide-up overflow-y-auto">
            <div className="flex items-center justify-between p-4 border-b border-surface-200">
              <h2 className="text-base font-bold text-slate-800">Filtros</h2>
              <button onClick={() => setMobileFiltersOpen(false)} className="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                <X className="w-5 h-5" />
              </button>
            </div>

            <form className="p-5 flex-1 space-y-5" onSubmit={(e) => e.preventDefault()}>
              <Select label="Área Profissional" {...register('area')}>
                <option value="">Todas as áreas</option>
                {AREAS.map((a) => (
                  <option key={a} value={a}>{a}</option>
                ))}
              </Select>
              <Select label="Nível" options={NIVEIS} {...register('nivel')} />
              <Select label="Modalidade" options={MODALIDADES} {...register('modalidade')} />
              <Select label="Contrato" options={TIPOS} {...register('tipo')} />
              <Input label="Salário Mínimo (R$)" type="number" {...register('salario_min')} />

              <div className="flex gap-2 pt-4">
                <Button type="button" variant="outline" fullWidth size="sm" onClick={clearFilters}>
                  Limpar
                </Button>
                <Button type="button" variant="primary" fullWidth size="sm" onClick={() => setMobileFiltersOpen(false)}>
                  Ver Resultados
                </Button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
