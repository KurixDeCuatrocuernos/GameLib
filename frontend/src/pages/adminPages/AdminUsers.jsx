import { useEffect, useState } from "react"
import MessageDisplay from "../../components/MessageDisplay"
import ConfirmComponent from "../../components/ConfirmComponent"

const AdminUsers = () => {

    const [usersList, setUsersList] = useState([])
    const [message, setMessage] = useState(null)
    const [isLoading, setIsLoading] = useState(false)
    const [search, setSearch] = useState('')
    const [filter, setFilter] = useState('all')
    const [sortBy, setSortBy] = useState('idAsc')
    // Constantes para el modal de confirmación
    const [showConfirmModal, setShowConfirmModal] = useState(false)
    const [confirmAction, setConfirmAction] = useState(null)
    const [confirmParams, setConfirmParams] = useState(null)
    const [confirmType, setConfirmType] = useState('danger')
    const [confirmTitle, setConfirmTitle] = useState('')
    const [confirmQuestion, setConfirmQuestion] = useState('')
    const [confirmExtraInfo, setConfirmExtraInfo] = useState('')

    useEffect(() => {
        getUsersData()
    },[])

    // Función para obtener los usuarios 
    const getUsersData = async () => {
        setIsLoading(true) 
        try{
            const response = await fetch('/api/functions/userFunctions/getAllUsers.php',{
                method: 'GET',
                credentials: 'include'
            })
            const data = await response.json()
            if (response.ok) {
                setUsersList(data)
            } else {
                setMessage({type:'error', text:`Hubo un error al recoger los datos: ${data.message}`})
            }
        } catch (error) {
            setMessage({type:'error', text:'Error al intentar recoger los datos'})
        } finally {
            setIsLoading(false)
        }

    }
    
    // Función para cambiar el role del usuario
    const changeUserRole = (userId, role) => {
        const newRole = role === 2 ? 'Usuario' : 'Administrador'
        setConfirmTitle('Cambiar rol')
        setConfirmQuestion(`¿Estás seguro de que quieres convertir a este usuario en ${newRole}?`)
        setConfirmExtraInfo('')
        setConfirmType('success')
        setConfirmAction(() => async () => { 
            setIsLoading(true)
            try {
                const response = await fetch('/api/functions/userFunctions/updateUserRoleById.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ userId, role })
                })
                const data = await response.json()
                if (response.ok) {
                    setMessage({ type: 'success', text: data.message })
                    setTimeout(() => setMessage(null), 2000)
                    getUsersData()
                } else {
                    setMessage({ type: 'error', text: data.message })
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Error al intentar modificar el role del usuario' })
            } finally {
                setIsLoading(false) // eliminamos la carga
                setShowConfirmModal(false) // Cerramos el modal
            }
        })
        setShowConfirmModal(true)
    }

    // Función para eliminar al usuario
    const deleteUser = (userId) => {
        setConfirmTitle('Eliminar usuario')
        setConfirmQuestion('¿Estás seguro de que quieres eliminar a este usuario?')
        setConfirmExtraInfo('Esta acción es irreversible. El usuario perderá todos sus datos.')
        setConfirmType('danger')
        setConfirmAction(() => async () => {
            setIsLoading(true)
            try {
                const response = await fetch('/api/functions/userFunctions/deleteUserById.php', {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: userId })
                })
                const data = await response.json()
                if (response.ok) {
                    setMessage({ type: 'success', text: data.message })
                    setTimeout(() => setMessage(null), 2000)
                    getUsersData()
                } else {
                    setMessage({ type: 'error', text: data.message })
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Error al intentar eliminar al usuario' })
            } finally {
                setIsLoading(false)
                setShowConfirmModal(false)
            }
        })
        setShowConfirmModal(true)
    }



    const resetPassword = (userId, userEmail) => {
        setConfirmTitle('Restablecer contraseña')
        setConfirmQuestion(`¿Estás seguro de que quieres enviar un enlace de restablecimiento a ${userEmail}?`)
        setConfirmExtraInfo('El usuario recibirá un correo con instrucciones para crear una nueva contraseña.')
        setConfirmType('warning')
        setConfirmAction(() => async () => {
            setIsLoading(true)
            try {
                // const response = await fetch('/api/functions/userFunctions/endpointEspecífico.php',{
                //     method: 'POST',
                //     credentials: 'include',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({
                //         id: userId,
                //     })
                // })
                // const data = await response.json()
                // if (response.ok) {
                //     setMessage({ type:'success', text:data.message })
                //     setTimeout(() => setMessage(null), 2000) // Eliminamos el mensaje de confirmación tras 2 segundos
                //     getUsersData() // Recargamos los datos
                // } else {
                //     setMessage({ type:'error', text:data.message })
                // }

                // Para Desarrollo simulamos el envío del correo
                //  No está hecho, pues requiere añadir un campo en la tabla users para que se le oblegue a modificar la contraseña al iniciar sesión
                setShowConfirmModal(false)
                await new Promise(resolve => setTimeout(resolve, 2000))
                setMessage({ type: 'success', text: `✅ Se ha enviado un correo a ${userEmail} para que pueda restablecer su contraseña` })
                setTimeout(() => setMessage(null), 2000)
            } catch (error) {
                setMessage({ type: 'error', text: 'Error al enviar el correo' })
            } finally {
                setIsLoading(false)
            }
        })
        setShowConfirmModal(true)
    }

    const resetFilters = () => {
        setFilter('all')
        setSearch('')
        setSortBy('idAsc')
    }

    // Constante para filtrar usuarios
    const filteredUsers = usersList.filter(user => {
        if (filter === 'user' && user.role !== 1) { return false } // Filtramos por usuario si filter está seleccionado como user
        if (filter === 'admin' && user.role !== 2) { return false } // Filtramos por usuario si filter está seleccionado como admin

        // Buscamos coincidencias con la búsqueda de search (si no está vacío)
        if (search.trim() !== '') {
            const searchLower = search.toLowerCase()
            return user.name.toLowerCase().includes(searchLower) ||
                   user.email.toLowerCase().includes(searchLower)
        }

        return true
    })
    
    // Constante para ordenar los usuarios filtrados
    const sortedUsers = [...filteredUsers].sort((a,b) => {
        if (sortBy === 'nameAsc') {
            return a.name.localeCompare(b.name)
        } else if (sortBy === 'nameDesc') {
            return b.name.localeCompare(a.name)
        } else if (sortBy === 'emailDesc') {
            return b.email.localeCompare(a.email)
        } else if (sortBy === 'emailAsc') {
            return a.email.localeCompare(b.email)
        } else if (sortBy === 'idAsc') {
            return a.id - b.id
        } else if (sortBy === 'idDesc') {
            return b.id - a.id
        }
        return 0
    })

    return (
        <div className="bg-green-950 min-h-screen p-6">
            {/* Contenido de la página */}
            <div className="container mx-auto">
                <h1 className="text-4xl text-center font-bold text-green-200 my-5">
                    Página de Administrador
                </h1>
                <h2 className="text-2xl text-center font-semibold italic text-green-300 my-5">
                    Aquí puedes ver y gestionar los usuarios de nuestra web
                </h2>

                {/* Mensajes de confirmación o error */}
                {message && <MessageDisplay message={message} setMessage={setMessage} />}

                {/* Modal de confirmación */}
                <ConfirmComponent 
                    show={showConfirmModal}
                    setShowConfirmModal={setShowConfirmModal}
                    title={confirmTitle}
                    question={confirmQuestion}
                    extraInformation={confirmExtraInfo}
                    confirmAction="Confirmar"
                    cancelAction="Cancelar"
                    type={confirmType}
                    onConfirm={confirmAction}
                />

                <div className="overflow-x-auto relative ">

                    {/* Spinner superpuesto a la tabla */}
                    {isLoading && (
                        <div className="absolute inset-0 bg-gray-800/50 flex justify-center items-center z-10 rounded-2xl">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-400"></div>
                        </div>
                    )}

                    {/* Filtros y Ordenamiento */}
                    <div className="bg-gray-800 rounded-lg p-4 mb-6 flex flex-col gap-4 justify-center items-center">
                        
                        <form className="flex flex-row justify-center items-center w-full mx-10 my-5">
                            <span className="text-green-200 w-auto">Buscar Usuario: </span>
                            <input type="search" className="bg-gray-400 text-black font-semibold italic w-auto mx-5 rounded hover:bg-gray-500 px-5"
                                value={search}
                                onChange={(e)=> setSearch(e.target.value)}
                                placeholder="Buscar Usuario..."
                            />
                        </form>
                        
                        <div className="flex items-center gap-2">
                            <span className="text-green-200">Role: </span>
                            <select 
                                value={filter} 
                                onChange={(e) => setFilter(e.target.value)}
                                className="bg-gray-700 text-white rounded px-3 py-1 hover:cursor-pointer hover:bg-gray-600"
                            >
                                <option value="all">Todos</option>
                                <option value="user">Usuario</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        
                        <div className="flex items-center gap-2">
                            <span className="text-green-200">Ordenar por:</span>
                            <select 
                                value={sortBy} 
                                onChange={(e) => setSortBy(e.target.value)}
                                className="bg-gray-700 text-white rounded px-3 py-1 hover:cursor-pointer hover:bg-gray-600"
                            >
                                <option value="idAsc">ID (1-100)</option>
                                <option value="idDesc">ID (100-1)</option>
                                <option value="nameAsc">Nombre (A-Z)</option>
                                <option value="nameDesc">Nombre (Z-A)</option>
                                <option value="emailAsc">Email (A-Z)</option>
                                <option value="emailDesc">Email (Z-A)</option>
                            </select>
                        </div>
                        
                        {/* Feedback del filtrado y la búsqueda */}
                        <div className="text-green-200 text-center">
                            <span>
                                Mostrando {sortedUsers.length} de {usersList.length} usuarios
                                {filter !== 'all' && (
                                    <span className="text-blue-300 ml-1">
                                        (rol: {filter === 'user' ? 'Usuario' : 'Administrador'})
                                    </span>
                                )}
                                {search && (
                                    <span className="text-yellow-300 ml-1">
                                        (buscando: "{search}")
                                    </span>
                                )}
                            </span>
                            {search !== '' || filter !== 'all' || sortBy !== 'idAsc' ? (
                                <button className="m-1 rounded hover:cursor-pointer hover:bg-gray-600"
                                    onClick={() => resetFilters()}>❌</button>
                            ) : ''}
                        </div>
                    </div>

                    <table className="w-full bg-gray-800 rounded-2xl border-2 border-green-100">
                        <thead className="bg-gray-900">
                            <tr>
                                <th className="p-3 text-left">ID</th>
                                <th className="p-3 text-left">USERNAME</th>
                                <th className="p-3 text-left">EMAIL</th>
                                <th className="p-3 text-center">ROLE</th>
                                <th className="p-3 text-center">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            {sortedUsers.map((user) => (
                                <tr key={user.id} className="border-b border-gray-700 hover:bg-gray-700">
                                    <td className="p-3">{user.id}</td>
                                    <td className="p-3">{user.name}</td>
                                    <td className="p-3">{user.email}</td>
                                    <td className="p-3 text-center">
                                        {user.role_name}
                                        <button className="bg-blue-600 text-white px-3 py-1 rounded text-sm w-fit hover:bg-blue-700 hover:cursor-pointer m-2"
                                            onClick={() => changeUserRole(user.id, user.role)}>
                                            Convertir en {user.role === 2 ? 'Usuario' : 'Administrador'}
                                        </button>
                                    </td>
                                    <td className="p-3">
                                        <button className="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 hover:cursor-pointer m-2"
                                            onClick={() => deleteUser(user.id)}>
                                            Eliminar
                                        </button>
                                        <button className="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700 hover:cursor-pointer"
                                            onClick={() => resetPassword(user.id, user.email)}>
                                            Reset Password
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    )
}
export default AdminUsers;