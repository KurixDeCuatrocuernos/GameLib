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

function App() {

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

        
        {/* Steam Callback */}
        <Route path="/steam-callback" element={<SteamCallback />} />
        
      </Routes>
    </>
  )
}

export default App
