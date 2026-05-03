import { useState, useRef } from "react"
import SearchBar from "../components/SearchBar"
import MessageDisplay from "../components/MessageDisplay"

const GameForm = () => {
    const [gameList, setGameList] = useState([]) // { game(name, url, date), platform}
    const [selectedGame, setSelectedGame] = useState(null)
    const [platform, setPlatform] = useState("")
    const [message, setMessage] = useState(null)
    const [isLoading, setIsLoading] = useState(false)

    const navigatorRef = useRef(null) // Referencia para resetear la barra de búsqueda de juegos desde aquí
    /**
     * Modifica la variable selectedGame por el juego elegido al buscarlo en IGDB
     * @param {*} gameObject 
     */
    const handleSelectGame = (gameObject) => {
        setSelectedGame(gameObject)
    }

    /**
     * Añade un juego a la lista gameList
     * @returns void
     */
    const addGameToList = () => {
        if (!selectedGame) {
            setMessage({ type:'error', text:'Por favor, selecciona un juego de la lista' })
            return
        }

        // Comprobamos que el juego no se haya añadido ya para el mismo proveedor antes de añadirlo a la lista
        const exists = gameList.some(item=>item.game.id === selectedGame.id && item.platform === platform)
        if (exists) {
            setMessage({ type:'error', text:`"${selectedGame.name}" ya está en la lista` })
            return
        } 
        
        setGameList([...gameList, {
            game: selectedGame,
            platform
        }]) // Añadimos el juego a la lista
        setSelectedGame(null) // Reseteamos el juego seleccionado
        if (navigatorRef.current) {
            navigatorRef.current.resetSearch() // Vaciamos el contenido de la barra de búsqueda
        }
        setMessage({ type:'success', text:`"${selectedGame.name}" añadido a la lista` }) // Mostramos un mensaje de confirmación
        setTimeout(() => setMessage(null), 5000) // Eliminamos el mensaje de confirmación pasados 5000 milisegundos (5 segundos)
    }

    /**
     * Elimina un juego añadido a la lista a partir de su lugar en el array
     * @param {int} index Posición del juego en el array
     */
    const removeGameFromList = (index) => {
        const deleteGameName = gameList[index].game.name
        const newList = gameList.filter((_,i) => i !== index) // Recogemos la lista que teníamos sin el id que elegimos quitar
        setGameList(newList)
        setMessage({ type:'success', text:`"${deleteGameName}" eliminado de la lista` }) // Mostramos un mensaje de confirmación
        setTimeout(() => setMessage(null), 2000) // Eliminamos el mensaje de confirmación pasados 2000 milisegundos (2 segundos) 
    }

    /**
     * Modifica la plataforma de un juego añadido
     * @param {int} index Representa el id del conjunto de un juego y plataforma en un array
     * @param {string} newPlatform representa la plataforma (provider) a la que se vinculará el juego (para el usuario actual)
     */
    const updatePlatform = (index, newPlatform) => {
        const newList = [...gameList] // Recogemos una copia de la lista actual
        newList[index].platform = newPlatform // Modificamos la plataforma en la copia
        setGameList(newList) // Actualizamos la copia de la lista
    }

    /**
     * Función para subir los juegos añadidos a la base de datos
     * @returns void
     */
    const submitList = async () => {
        // Revisamos que haya al menos un juego que añadir
        if (gameList.length === 0) {
            setMessage({ 
                type:'error', 
                text:'No hay juegos en la lista que añadir' 
            })
            return
        }

        setIsLoading(true) // Cambiamos loading a true mientras procesamos la información en el backend (servirá de filtro en el html)
        try{
            // const url = '/api/functions/usersGames/insertGamesList.php'
            // console.log("Llamando a:", url)
            // console.log("Datos enviados:", { games: gameList })

            const response = await fetch('/api/functions/usersGames/insertGamesList.php', {
                method:'POST',
                credentials:'include',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ games: gameList })
            })

            // const text = await response.text()
            // console.log("Respuesta RAW:", text)
        
            const result = await response.json() // Decodificamos los resultados del Json


            if (response.ok) {
                setMessage({ 
                    type:'success', 
                    text:`✅ ${result.inserted} juegos añadidos, ${result.already_exists} ya existían, ${result.failed} fallidos` 
                }) // Confirmamos el número de juegos añadidos 
                setGameList([]) // Vaciamos la lista de juegos
                setTimeout(()=>setMessage(null),5000) // Borramos el mensaje de confirmación pasados 5000 milisegundos (5 segundos)
            } else {
                setMessage({ 
                    type:'error', 
                    text: result.message || 'Error al añadir los juegos' 
                }) // Mostramos un mensaje de error para dar feedback del backend
            }
        } catch (error) {
            console.error("Error: ", error)
            setMessage({ 
                type:'error', 
                text:'Error de conexión con el servidor' 
            }) // Mostramos el mensaje de error al conectar para dar feedback 
        } finally {
            setIsLoading(false) // En cualquier caso restablecemos loading a false
        }
    }
    
    
    return(<div className="flex flex-col w-auto h-auto m-20 bg-gray-900 border-green-100 border-2 rounded-2xl p-8">
        <h1 className="text-center font-extrabold text-2xl mb-8">Añadir juegos manualmente</h1>
        
        <MessageDisplay message={message} setMessage={setMessage} />
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            {/* Columna izquierda: Buscador */}
            <div className="bg-gray-800 p-6 rounded-xl">
                <h2 className="text-xl font-bold text-green-400 mb-4">Buscar juego</h2>
                
                <SearchBar onSelectGame={handleSelectGame} ref={navigatorRef} placeholder="Escribe el nombre del juego..." />
                
                {selectedGame && (
                    <div className="mt-3 p-2 bg-green-800 rounded-lg">
                        <div className="flex items-center gap-3">
                            {selectedGame.cover.url && (
                                <img 
                                    src={selectedGame.cover.url} 
                                    alt={selectedGame.name} 
                                    className="w-12 h-12 rounded object-cover"
                                />
                            )}
                            <div>
                                <p className="text-green-200">Seleccionado:</p>
                                <p className="font-bold text-white">{selectedGame.name}</p>
                                {selectedGame.releaseDate && (
                                    <p className="text-xs text-gray-300">{selectedGame.releaseDate}</p>
                                )}
                            </div>
                        </div>
                    </div>
                )}
                
                <label className="block text-green-200 mt-4 mb-2">Plataforma:</label>
                <select 
                    value={platform} 
                    onChange={(e) => setPlatform(e.target.value)}
                    className="w-full p-2 rounded bg-gray-700 text-white hover:bg-gray-500 hover:cursor-pointer"
                >
                    <option value="steam">Steam</option>
                    <option value="epic">Epic Games</option>
                    <option value="gog">GOG</option>
                </select>
                
                <button 
                    onClick={addGameToList}
                    disabled={!selectedGame}
                    className="w-full mt-4 bg-blue-600 text-white font-bold py-2 px-4 rounded disabled:opacity-50 hover:bg-blue-700 hover:cursor-pointer"
                >
                    Añadir a la lista
                </button>
            </div>
            
            {/* Columna derecha: Lista de juegos */}
            <div className="bg-gray-800 p-6 rounded-xl">
                <h2 className="text-xl font-bold text-green-400 mb-4">
                    Lista de juegos ({gameList.length})
                </h2>
                
                {gameList.length === 0 ? (
                    <p className="text-gray-400 text-center py-8">No hay juegos en la lista. Busca y añade juegos.</p>
                ) : (
                    <div className="max-h-96 overflow-y-auto">
                        <table className="w-full text-left">
                            <thead className="bg-gray-700 sticky top-0">
                                <tr>
                                    <th className="p-2">Juego</th>
                                    <th className="p-2">Plataforma</th>
                                    <th className="p-2 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {gameList.map((item, index) => (
                                    <tr key={index} className="border-b border-gray-700">
                                        <td className="p-2">
                                            <div className="flex items-center gap-2">
                                                {item.game.cover.url && (
                                                    <img src={item.game.cover.url} alt={item.game.name} className="w-8 h-8 rounded object-cover" />
                                                )}
                                                <span>{item.game.name}</span>
                                            </div>
                                            </td>
                                        <td className="p-2">
                                            <select 
                                                value={item.platform} 
                                                onChange={(e) => updatePlatform(index, e.target.value)}
                                                className="bg-gray-700 text-white p-1 rounded text-sm hover:bg-gray-500 hover:cursor-pointer"
                                            >
                                                <option value="steam">Steam</option>
                                                <option value="epic">Epic</option>
                                                <option value="gog">GOG</option>
                                            </select>
                                            </td>
                                        <td className="p-2 text-center">
                                            <button 
                                                onClick={() => removeGameFromList(index)}
                                                className="bg-red-600 text-white px-2 py-1 rounded text-sm hover:bg-red-700 hover:cursor-pointer"
                                            >
                                                Eliminar
                                            </button>
                                            </td>
                                        </tr>
                                ))}
                            </tbody>
                            </table>
                    </div>
                )}
                
                <button 
                    onClick={submitList}
                    disabled={isLoading || gameList.length === 0}
                    className="w-full mt-4 bg-green-600 text-white font-bold py-2 px-4 rounded disabled:opacity-50 hover:bg-green-700 hover:cursor-pointer"
                >
                    {isLoading ? "Añadiendo juegos..." : `Añadir ${gameList.length} juego${gameList.length !== 1 ? 's' : ''} a mi biblioteca`}
                </button>
            </div>
        </div>
    </div>)
} 
export default GameForm 