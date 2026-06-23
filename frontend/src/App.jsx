import { BrowserRouter } from 'react-router-dom'
import { AuthProvider } from '@/contexts/AuthContext'
import AppRoutes from '@/routes'
import Header from '@/components/Header'
import Footer from '@/components/Footer'

function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <div className="flex flex-col min-h-screen">
          {/* Header Global */}
          <Header />

          {/* Conteúdo Principal Dinâmico */}
          <main className="flex-1 bg-surface-50">
            <AppRoutes />
          </main>

          {/* Footer Global */}
          <Footer />
        </div>
      </AuthProvider>
    </BrowserRouter>
  )
}

export default App
