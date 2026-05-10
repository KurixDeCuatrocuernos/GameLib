import { useState, useEffect } from "react"
import MessageDisplay from "../../components/MessageDisplay"
import ConfirmComponent from "../../components/ConfirmComponent"

const AdminGames = () => {
    const [gamesList, setGamesList] = useState([])
    const [message, setMessage] = useState(null)
    const [isLoading, setIsLoading] = useState(false)
    const [search, setSearch] = useState('')
    const [sortBy, setSortBy] = useState('idAsc')
    const [selectedGame, setSelectedGame] = useState(null)
    const [showConfirmModal, setShowConfirmModal] = useState(false)

    useEffect(() => {
        getGamesData()
    },[])

    /**
     * Función para obtener los juegos de la base de datos
     */
    const getGamesData = async () => {
        setIsLoading(true) 
        try{
            const response = await fetch('/api/functions/gameFunctions/getAllGames.php',{
                method: 'GET',
                credentials: 'include'
            })
            const data = await response.json()
            if (response.ok) {
                setGamesList(data)
            } else {
                setMessage({type:'error', text:`Hubo un error al recoger los datos: ${data.message}`})
            }
        } catch (error) {
            setMessage({type:'error', text:'Error al intentar recoger los datos'})
        } finally {
            setIsLoading(false)
        }

    }



    /**
     * Función para confirmar la eliminación del juego
     * @param {int} gameId Id del juego a borrar 
     */
    const deleteGame = async (gameId) => {
        const game = gamesList.find(g => g.id === gameId)
        setSelectedGame(game)
        setShowConfirmModal(true)
    }

    /**
     * Función para eliminar el juego
     * @returns void
     */
    const confirmDelete = async () => {
        if (!selectedGame) return
        
        setIsLoading(true)
        try {
            const response = await fetch('/api/functions/gameFunctions/deleteGame.php', {
                method: 'DELETE',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: selectedGame.id })
            })
            const data = await response.json()
            if (response.ok) {
                setMessage({ type: 'success', text: data.message })
                setTimeout(() => setMessage(null), 2000)
                getGamesData()
            } else {
                setMessage({ type: 'error', text: data.message })
            }
        } catch (error) {
            setMessage({ type: 'error', text: 'Error al intentar eliminar el juego' })
        } finally {
            setIsLoading(false)
            setShowConfirmModal(false)
            setSelectedGame(null)
        }
    }

    /**
     * Función para reiniciar los filtros y la búsqueda
     */
    const resetFilters = () => {
        setSearch('')
        setSortBy('idAsc')
    }

    /**
     * Constante para filtrar juegos
     */
    const filteredGames = gamesList.filter(game => {
        // Buscamos coincidencias con la búsqueda de search (si no está vacío)
        if (search.trim() !== '') {
            const searchLower = search.toLowerCase()
            return game.name.toLowerCase().includes(searchLower)
        }

        return true
    })
    
    /**
     * Constante para ordenar los juegos filtrados
     */
    const sortedGames = [...filteredGames].sort((a,b) => {
        if (sortBy === 'titleAsc') {
            return a.name.localeCompare(b.name)
        } else if (sortBy === 'titleDesc') {
            return b.name.localeCompare(a.name)
        } else if (sortBy === 'dateAsc') {
            return new Date(b.release_date) - new Date(a.release_date)
        } else if (sortBy === 'dateDesc') {
            return new Date(a.release_date) - new Date(b.release_date)
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
                {/* Título */}
                <h1 className="text-4xl text-center font-bold text-green-200 my-5">
                    Página de Administrador
                </h1>
                {/* Subtítulo */}
                <h2 className="text-2xl text-center font-semibold italic text-green-300 my-5">
                    Aquí puedes ver y gestionar los juegos de nuestra web
                </h2>
                
                {/* Mensaje de error o confirmación */}
                {message && <MessageDisplay message={message} setMessage={setMessage} />}
                
                {/* Modal para confirmar el borrado del juego con información */}
                <ConfirmComponent 
                    show={showConfirmModal}
                    setShowConfirmModal={setShowConfirmModal}
                    title="Eliminar juego"
                    question={`¿Estás seguro de que quieres eliminar <span className="font-bold text-white">${selectedGame?.name}</span>?`}
                    extraInformation={selectedGame?.user_count > 0 ? 
                        `⚠️ Este juego está en la biblioteca de <span className="font-bold">${selectedGame.user_count}</span> usuario${selectedGame.user_count !== 1 ? 's' : ''}. Al eliminarlo, desaparecerá de sus bibliotecas.` : 
                        `⚠️ Este juego no está en ninguna biblioteca. Puedes eliminarlo sin afectar a ningún usuario.`
                    }
                    confirmAction="Eliminar"
                    cancelAction="Cancelar"
                    type="danger"
                    onConfirm={confirmDelete}
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
                            <span className="text-green-200 w-auto">Buscar Juego: </span>
                            <input type="search" className="bg-gray-400 text-black font-semibold italic w-auto mx-5 rounded hover:bg-gray-500 px-5"
                                value={search}
                                onChange={(e)=> setSearch(e.target.value)}
                                placeholder="Buscar Juego..."
                            />
                        </form>
                        
                        <div className="flex items-center gap-2">
                            <span className="text-green-200">Ordenar por:</span>
                            <select 
                                value={sortBy} 
                                onChange={(e) => setSortBy(e.target.value)}
                                className="bg-gray-700 text-white rounded px-3 py-1 hover:cursor-pointer hover:bg-gray-600"
                            >
                                <option value="idAsc">ID (1 - 100)</option>
                                <option value="idDesc">ID (100 - 1)</option>
                                <option value="titleAsc">Título (A-Z)</option>
                                <option value="titleDesc">Título (Z-A)</option>
                                <option value="dateAsc">Fecha (New-Old)</option>
                                <option value="dateDesc">Fecha (Old-New)</option>
                            </select>
                        </div>
                        
                        {/* Feedback del filtrado y la búsqueda */}
                        <div className="text-green-200 text-center">
                            <span>
                                Mostrando {sortedGames.length} de {gamesList.length} juegos
                                {search && (
                                    <span className="text-yellow-300 ml-1">
                                        (buscando: "{search}")
                                    </span>
                                )}
                            </span>
                            {search !== '' || sortBy !== 'idAsc' ? (
                                <button className="m-1 rounded hover:cursor-pointer hover:bg-gray-600"
                                    onClick={() => resetFilters()}>❌</button>
                            ) : ''}
                        </div>
                    </div>

                    <table className="w-full bg-gray-800 rounded-2xl border-2 border-green-100">
                        <thead className="bg-gray-900">
                            <tr>
                                <th className="p-3 text-left">ID</th>
                                <th className="p-3 text-left">TITLE</th>
                                <th className="p-3 text-left">COVER</th>
                                <th className="p-3 text-center">USED BY</th>
                                <th className="p-3 text-center">RELEASE DATE</th>
                                <th className="p-3 text-center">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            {sortedGames.map((game) => (
                                <tr key={game.id} className="border-b border-gray-700 hover:bg-gray-700">
                                    <td className="p-3">{game.id}</td>
                                    <td className="p-3">{game.name}</td>
                                    <td className="p-3">
                                        <img src={game.cover} alt={game.name} className="w-16 h-20 object-cover rounded" onError={(e) => {e.target.src = "https://placehold.co/300x450?text=No+Cover"}}/>
                                    </td>
                                    <td className="p-3 text-center">
                                        <span className={`px-2 py-1 rounded-full text-xs font-bold ${
                                            game.user_count > 0 ? 'bg-yellow-600 text-white' : 'bg-gray-600 text-gray-300'
                                        }`}>
                                            {game.user_count}
                                        </span>
                                    </td>
                                    <td className="p-3">{game.release_date}</td>
                                    <td className="p-3">
                                        <button className="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 hover:cursor-pointer m-2"
                                            onClick={() => deleteGame(game.id)}>
                                            Eliminar
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

export default AdminGames