import { useEffect, useState } from "react"
import MessageDisplay from "../components/MessageDisplay"

const Profile = () => {
    const [userData, setUserData] = useState(null)
    const [message, setMessage] = useState(null)

    const [username, setUsername] = useState('')
    const [email, setEmail] = useState('')
    const [oldPassword, setOldPassword] = useState('')
    const [newPassword, setNewPassword] = useState('')
    const [showPass, setShowPass] = useState('password')

    const [errorUserData, setErrorUserData] = useState('')
    const [errorPass, setErrorPass] = useState('')
    const [isLoading, setIsLoading] = useState(false)

    useEffect(() => {
        getUserData() // Recogemos los datos del usuario al recargar la vista
    }, [])

    useEffect(() => {
        if (userData) {
            setUsername(userData.name || '')
            setEmail(userData.email || '')
        }
    }, [userData])

    const getUserData = async () => {
        setErrorPass('')
        setErrorUserData('')

        setIsLoading(true)
        try {
            const response = await fetch('/api/functions/userFunctions/getUserProfile.php', {
                method: 'GET',
                credentials: 'include',
            })
            const data = await response.json()
            if (response.ok) {
                setUserData(data.data)
                // setMessage({ type: 'success', text: 'Datos del usuario recogidos' }) // Confirmación innecesaria, sólo para desarrollo
                setTimeout(() => setMessage(null), 3000)
            } else {
                setMessage({ type: 'error', text: data.message || "Error al cargar datos" })
            }
        } catch (error) {
            console.error("Hubo un error al conectar con el Backend: ", error)
            setMessage({ type: 'error', text: "Error de Conexión" })
        } finally {
            setIsLoading(false)
        }
    }

    const updateProfile = async () => {
        // Validaciones básicas
        if (username.trim() === '') {
            setErrorUserData("El nombre de usuario no puede estar vacío")
            return
        }
        if (email.trim() === '' || !email.includes('@')) {
            setErrorUserData("Introduce un email válido")
            return
        }

        // Preparar datos a enviar
        const updateData = {}
        if (username !== userData?.name) updateData.username = username
        if (email !== userData?.email) updateData.email = email
        if (oldPassword && newPassword) {
            if (newPassword.length < 5) {
                setErrorPass("La nueva contraseña debe tener al menos 6 caracteres")
                return
            }

            updateData.oldPassword = oldPassword
            updateData.newPassword = newPassword
        } else if ((!oldPassword && newPassword) || (!newPassword && oldPassword)) {
            setErrorPass("Ambos campos deben estar completos")
        }

        if (Object.keys(updateData).length === 0) {
            setMessage({ type: 'success', text: "No hay cambios" })
            setTimeout(()=> setMessage(null), 2000) // El mensaje desaparece tras 2 segundos
            return
        }

        setIsLoading(true)
        try {
            const response = await fetch('/api/functions/userFunctions/updateUserData.php', {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updateData)
            })
            const data = await response.json()
            if (response.ok) {
                // setMessage({ type: 'success', text: data.message }) // Sólo para desarrollo, si todo sale bien se recarga la página
                setOldPassword('')
                setNewPassword('')
                window.location.reload() // Es preciso para que el navbar también se recargue y se recojan los nuevos datos, comentar esta línea en desarrollo para ver errores
            } else if (response.status === 409) {
                setErrorUserData(data.message)
            } else if (response.status === 403) {
                setErrorPass(data.message)
            } else {
                setMessage({ type: 'error', text: data.message })
            }
        } catch (error) {
            console.error("Error al actualizar: ", error)
            setMessage({ type: 'error', text: "Error de conexión" })
        } finally {
            setIsLoading(false)
        }
    }

    const toggleShowPass = () => {
        setShowPass(showPass === 'password' ? 'text' : 'password')
    }

    if (isLoading && !userData) {
        return (
            <div className="flex justify-center items-center min-h-screen bg-green-950">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-400"></div>
            </div>
        )
    }

    const deleteAccount = async () => {
        const isDelete = confirm("¿Are you sure you want to delete your account?\n(This action can't be undone)")
        if (isDelete === true) {
            try {
                const response = await fetch('/api/functions/userFunctions/deleteAccount.php', {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                const data = await response.json()
                if (response.ok) {
                    window.location.href = "/login" // Redirigimos al Login pues ya no hay cuenta, usamos window.location porque useNavigate() no funciona correctamente en este caso
                } else {
                    setMessage({ type:'error', text:data.message })
                }

            } catch (error) {
                setMessage({ type:'error', text:"Hubo un error al borrar la cuenta" })
            }
        } else {
            alert("Borrado Cancelado")
        }
    }

    return (
        <div className="bg-green-950 min-h-screen p-6">
            <div className="container mx-auto max-w-2xl">
                <h1 className="text-3xl font-bold text-green-200 mb-6 text-center">
                    Mi Perfil
                </h1>
                
                <MessageDisplay message={message} setMessage={setMessage} />
                
                {userData ? (
                    <div className="bg-gray-800 rounded-xl p-6 shadow-lg border-2 border-green-100">
                        {/* Datos personales */}
                        <form className="space-y-4" onSubmit={(e) => e.preventDefault()}>
                            <h2 className="text-xl text-center font-bold text-green-300 mb-4">Datos personales</h2>
                            
                            {/* Mensaje de error */}
                            {errorUserData && <p className="text-red-400 text-sm ml-32">{errorUserData}</p>}
                            
                            {/* Cambiar nombre de usuario */}
                            <div className="flex flex-col md:flex-row md:items-center gap-2">
                                <label className="text-green-200 w-32 font-semibold">Nombre:</label>
                                <input 
                                    type="text" 
                                    value={username} 
                                    onChange={(e) => setUsername(e.target.value)}
                                    className="flex-1 bg-gray-700 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                />
                            </div>
                            {/* Cambiar email */}
                            <div className="flex flex-col md:flex-row md:items-center gap-2">
                                <label className="text-green-200 w-32 font-semibold">Email:</label>
                                <input 
                                    type="email" 
                                    value={email} 
                                    onChange={(e) => setEmail(e.target.value)}
                                    className="flex-1 bg-gray-700 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                />
                            </div>
                            
                            {/* Cambiar contraseña */}
                            <div className="mt-6 pt-4 border-t border-gray-600">
                                <h2 className="text-xl text-center font-bold text-green-300 mb-4">Cambiar contraseña</h2>
                                {/* Mensaje de error */}
                                {errorPass && <p className="text-red-400 text-sm ml-32">{errorPass}</p>}
                                
                                <div className="flex flex-col md:flex-row md:items-center gap-2 mb-3">
                                    <label className="text-green-200 w-32 font-semibold">Contraseña actual:</label>
                                    {/* Contraseña antigua */}
                                    <div className="flex flex-row gap-1">
                                        <input 
                                            type={showPass} 
                                            value={oldPassword} 
                                            onChange={(e) => setOldPassword(e.target.value)}
                                            className="flex-1 bg-gray-700 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                        />
                                        <button 
                                            type="button"
                                            onClick={toggleShowPass}
                                            className="bg-gray-600 w-15 text-white flex flex-col justify-center items-center px-3 py-2 rounded-lg hover:cursor-pointer hover:bg-gray-500"
                                        >
                                            <img src={showPass === 'password' ? '/ojo.png' : '/ojo-cruzado.png'} alt="" className="h-5"/>
                                        </button>
                                    </div>
                                    
                                </div>
                                
                                <div className="flex flex-col md:flex-row md:items-center gap-2">
                                    <label className="text-green-200 w-32 font-semibold">Nueva contraseña:</label>
                                    {/* Contraseña nueva */}
                                    <div className="flex flex-row gap-1">
                                        <input 
                                            type={showPass} 
                                            value={newPassword} 
                                            onChange={(e) => setNewPassword(e.target.value)}
                                            className="flex-1 bg-gray-700 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                        />
                                        <button 
                                            type="button"
                                            onClick={toggleShowPass}
                                            className="bg-gray-600 w-15 text-white flex flex-col justify-center items-center px-3 py-2 rounded-lg hover:cursor-pointer hover:bg-gray-500"
                                        >
                                            <img src={showPass === 'password' ? '/ojo.png' : '/ojo-cruzado.png'} alt="" className="h-5"/>
                                        </button>
                                    </div>
                                    
                                </div>
                            </div>
                            
                            <div className="flex justify-center gap-4 mt-6 pt-4 border-t border-gray-600">
                                <button 
                                    type="button"
                                    onClick={updateProfile}
                                    disabled={isLoading}
                                    className="bg-green-600 text-white font-bold py-2 px-6 rounded-lg disabled:opacity-50 hover:bg-green-700 hover:cursor-pointer"
                                >
                                    {isLoading ? "Guardando..." : "Guardar cambios"}
                                </button>
                            </div>
                        </form>
                        {/* Borrar Cuenta */}
                        <div className="flex flex-col justify-center items-center m-5 gap-4 mt-6 pt-4 border-t border-gray-600">
                            <h3 className="text-gray text-center">¿Quieres eliminar tu cuenta y toda la información almacenada en nuestra aplicación?</h3>
                            <button className="bg-red-600 text-red-100 p-2 rounded-2xl border-2 m-5 border-red-950 hover:cursor-pointer hover:bg-red-900 hover:text-red-300"
                                onClick={deleteAccount}>Eliminar Cuenta</button>
                        </div>
                    </div>
                ) : (
                    <div className="text-center text-red-400 py-10">
                        <p>Error crítico: No se pudieron cargar los datos del usuario</p>
                    </div>
                )}
            </div>
        </div>
    )
}

export default Profile