// Importamos funciones
import { useState } from 'react'
import { Routes, Route } from 'react-router-dom'
// Importamos nuestras páginas
import Navbar from './components/Navbar'
import Dashboard from './pages/Dashboard'
import Login from './pages/Login'
import Signup from './pages/Signup'

function App() {

  return (
    <>
    <Navbar />
    {/* Rutas de nuestro Frontend */}
      <Routes>
        {/* Inicio */}
        <Route path="/" element={<Dashboard />} /> 
        {/* Login */}
        <Route path='/login' element={<Login />} />
        {/* Registro */}
        <Route path='/signup' element={<Signup />} />
        
      </Routes>
    </>
  )
}

export default App
