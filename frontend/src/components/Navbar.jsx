import { useEffect, useState } from "react"
import { useAuth } from '../contexts/AuthContext'


const Navbar = () => {

    const { user, logout, loading } = useAuth()

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
                <div className="flex flex-row justify-between items-center m-5">
                    <div className="flex flex-row items-center">
                        <a href="/">
                            <img className="w-20 me-5" src="/gamepad-Icon.png" alt="Logo" />
                        </a>
                        <a className="text-4xl font-bold italic text-green-200" href="/">
                            Game Library
                        </a>
                    </div>
                </div>
            </header>
        )
    }

    return (<header>
        <div >
            <div className="flex flex-row justify-between items-center m-5">
                <div>
                    <a href="/">
                        <img className="w-20 me-5"
                        src="/gamepad-Icon.png" alt="Logo del proyecto" />
                    </a>
                <a className="text-4xl font-bold italic text-green-200" 
                    href="/" >Game Library</a>
                </div>

                {user ? (  
                    <div className="flex flex-row w-30">
                        <div className="flex flex-col">
                            <p className="text-2xl mr-5">{user.username}</p>
                            <p className="">{user.role}</p>
                            <button type="button" onClick={cerrarSesion}>Cerrar Sesión</button>
                        </div>
                        <img src="/gamepad-Icon.png" alt="User's Picture" className="w-15"/>
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
        <nav className="flex flex-row gap-4 justify-evenly items-center w-full text-2xl font-bold italic">
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
                    <a className="hover:text-green-950" href="/profile">Mi Perfil</a>
                    <p>/</p>
                </>
            )}
            <a className=" hover:text-green-950" 
                href="">Enlace 3</a>
            <p>/</p>
            <a className=" hover:text-green-950" 
                href="">Enlace 4</a>
            <p>/</p>
            <a className=" hover:text-green-950" 
                href="">Enlace 5</a>
        </nav>

    </header>)
}

export default Navbar