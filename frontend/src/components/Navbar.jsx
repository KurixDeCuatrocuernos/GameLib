import { useEffect, useState } from "react"
import { useAuth } from '../contexts/AuthContext'
import { useNavigate } from "react-router-dom"

const Navbar = () => {

    const { user, logout, loading } = useAuth()
    const navigate = useNavigate()

    async function cerrarSesion() {
        const confirmar = confirm("¿Seguro que quieres cerrar la sesión?")
        if (confirmar) {
            await logout()
            window.location.href = '/'
        }
    }


    if (loading) {
        return (
            <header>
                <div className="flex flex-row justify-between items-center m-5 me-5 ">
                    <div className="flex flex-row items-center">
                        <a href="/">
                            <img className="w-20 h-20" src="/gamepad-Icon.png" alt="Logo" />
                        </a>
                        <a className="text-4xl font-bold italic text-green-200" href="/">
                            Game Library
                        </a>
                    </div>
                </div>
            </header>
        )
    }

    return (<header className="w-full">
        <div className="w-full">
            <div className="flex flex-row justify-between items-center bg-green-950 p-5 w-full">
                {/* TITULO */}
                <div className="flex flex-row items-center gap-2 w-auto">
                    <a href="/">
                        <img className="w-20 h-20"
                        src="/gamepad-Icon.png" alt="Logo del proyecto" />
                    </a>
                    <a className="text-4xl font-bold italic text-green-200" 
                        href="/" >Game Library</a>
                </div>
                
                {/* Botón para Admin */}
                {user?.role === 'admin' && (
                    <div className="bg-green-200 rounded-3xl h-auto p-2 hover:cursor-pointer hover:opacity-50" onClick={()=>navigate('/admin')}>
                        <img src="/ajustes.png" alt="Icono de ajustes" className="h-7" />    
                    </div>
                )}

                {/* SESIÓN DEL USUARIO */}
                {user ? (  
                    <div className="flex flex-row">
                        <div className="flex flex-col justify-center items-center">
                            <p className="text-2xl font-bold mr-5">{user.username}</p>
                            <p className="italic font-bold">{user.role}</p>
                            <button type="button" onClick={cerrarSesion} 
                                className="bg-green-100 text-black font-bold w-30 py-2 rounded-3xl hover:cursor-pointer hover:bg-green-950 hover:text-green-50">
                                Cerrar Sesión</button>
                        </div>
                        <img src="/gamepad-Icon.png" alt="User's Picture" className="w-20 h-20"/>
                    </div>)
                : (
                    <div className="flex gap-2">
                        <a href="/login" className="text-green-200 hover:text-green-400">Login</a>
                        <span className="text-green-200">|</span>
                        <a href="/signup" className="text-green-200 hover:text-green-400">Signup</a>
                    </div>
                )}

            </div>
            
        </div>
        {/* NAV */}
        <nav className="flex flex-row gap-4 justify-evenly items-center w-auto text-xl font-bold italic">
            {!user ? (
                <>
                    <a className="hover:text-green-950" href="/login">Log In</a>
                    <p>/</p>
                    <a className="hover:text-green-950" href="/signup">Sign Up</a>
                    <p>/</p>
                </>
            ) : (
                <>
                    <a className="hover:text-green-950" href="/library">Mi Biblioteca</a>
                    <p>/</p>
                </>
            )}
            <a className=" hover:text-green-950" 
                href="/synchronize">Synchronize Libraries</a>
        </nav>

    </header>)
}

export default Navbar