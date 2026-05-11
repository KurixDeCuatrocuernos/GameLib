// Importamos funciones
import { Children, useState } from 'react'
import { Navigate, Routes, Route } from 'react-router-dom'
import { useAuth } from './contexts/AuthContext' // Importamos el contexto
// Importamos nuestras páginas
import Navbar from './components/Navbar'
import Dashboard from './pages/Dashboard'
import Login from './pages/Login'
import Signup from './pages/Signup'
import Synchronize from './pages/Synchronize'
import SteamCallback from './pages/SteamCallback'
import GameForm from './pages/GameForm'
import MyLibrary from './pages/MyLibrary'
import Profile from './pages/Profile'
import AdminDashboard from './pages/adminPages/AdminDashboard'
import AdminUsers from './pages/adminPages/AdminUsers'
import AdminGames from './pages/adminPages/AdminGames'
import Footer from './components/Footer'
import ErrorPage from './pages/errorPages/ErrorPage'

function App() {

  const { user, loading } = useAuth()

  // Componente que protege rutas privadas (requieren estar logueado)
  const PrivateRoute = ({children}) => {
    const { user, loading } = useAuth()
    if (loading) return <div>Cargando...</div> // Se puede crear un componente para mostrar la carga
    if (!user) return <Navigate to="/login" replace /> // Si no tiene acceso redirigimos a Login
    return children
  }

  // Componente que protege rutas de invitados (login, signup)
  const GuestRoute = ({ children }) => {
      const { user, loading } = useAuth()
      
      if (loading) return <div>Cargando...</div> // Se puede crear un componente para mostrar la carga
      if (user) return <Navigate to="/" replace /> // Si no tiene acceso redirigimos a Inicio
      return children
  }

  // Compoente que protege las rutas de administrador
  const AdminRoute = ({ children }) => {
    if (loading) return <div className="text-center py-20">Cargando...</div>
    if (!user) return <Navigate to="/login" replace />
    // Verificamos que el usuario tenga rol de administrador (rol = 2)
    if (user.role !== 'admin') return <Navigate to="/" replace />
    return children
  }

  return (
    <>
    <Navbar />
    {/* Rutas de nuestro Frontend */}
      <Routes>
        {/* Inicio */}
        <Route path="/" element={<Dashboard />} /> 
        {/* Login */}
        <Route path='/login' element={
          <GuestRoute>
            <Login />
          </GuestRoute> } 
        />
        {/* Registro */}
        <Route path='/signup' element={
          <GuestRoute>
            <Signup />
          </GuestRoute> } 
        />
        {/* Vincular Librerías */}
        <Route path='/synchronize' element={
          <PrivateRoute>
            <Synchronize />
          </PrivateRoute> } 
        />
        {/* Insertar juegos manualmente */}
        <Route path='/game-form' element={
          <PrivateRoute>
            <GameForm />
          </PrivateRoute>
        } />
        {/* Biblioteca del usuario */}
        <Route path='/library' element={
          <PrivateRoute>
            <MyLibrary />
          </PrivateRoute>
        } />
        {/* Página de perfil del usuario */}
        <Route path='/profile' element={
          <PrivateRoute>
            <Profile />
          </PrivateRoute>
        } />
        {/* Página de administrador */}
        <Route path='/admin' element={
          <AdminRoute>
            <AdminDashboard />
          </AdminRoute>
        } />
        {/* Página de administrador de usuarios */}
        <Route path='/admin/users' element={
          <AdminRoute>
            <AdminUsers />
          </AdminRoute>
        } />
        {/* Página de administrador de juegos */}
        <Route path='/admin/games' element={
          <AdminRoute>
            <AdminGames />
          </AdminRoute>
        } />

        {/* Página de error */}
        <Route path='/error' element={
          <ErrorPage />
        } />
        {/* Redirección para cualquier ruta que no se haya configurado */}
        <Route path="*" element={
          <Navigate to="/error?code=404" replace />
        } />


        {/* Steam Callback */}
        <Route path="/steam-callback" element={<SteamCallback />} />
        
      </Routes>
      
      <Footer />
    </>
  )
}

export default App
