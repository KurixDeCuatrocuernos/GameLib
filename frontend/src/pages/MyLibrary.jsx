import { useEffect, useState } from "react"
import { useNavigate } from "react-router-dom"
import Game from "../components/Game"
import MessageDisplay from "../components/MessageDisplay"

const MyLibrary = () => {
    const [gameList, setGameList] = useState([])
    const [message, setMessage] = useState(null)
    const [isLoading, setIsLoading] = useState(false)
    const [filter, setFilter] = useState("all")
    const [sortBy, setSortBy] = useState("name")
    const [search, setSearch] = useState("")

    const navigate = useNavigate()

    useEffect(()=>{
        getUserGames()
    },[])

    /**
     * Esta Función recoge todos los juegos agregados por el usuario mediante el endpoint getUserGames.php, de manera asíncrona
     */
    const getUserGames = async () => {
        setIsLoading(true)
        try {
            const response = await fetch('/api/functions/usersGames/getUserGames.php', {
                method: 'GET',
                credentials: 'include',
            })
            const data = await response.json()
            if (response.ok) {
                setGameList(data)
                // console.log(data) // para ver los datos recibidos
                setMessage({ type:'success', text:'Juegos recogidos' }) // Mensaje de confirmación (opcional)
                setTimeout(() => setMessage(null),2000) // cerramos el mensaje de confirmación pasados dos segundos
            } else {
                console.error("Error: ",data.message)
                setMessage({ type:'error', text:`Error: ${data.message}` }) // Si ha habido error mostraremos el mensaje del Backend    
            }
        } catch (error) {
            console.error("Error de conexión: ",error)
            setMessage({ type:'error', text:"Error de Conexión" })    

        } finally {
            setIsLoading(false)
        }
    }

    /**
     * Filtro por plataformas
     */
    const filteredByPlatform = gameList.filter(game => {
        if (filter === 'all') return true
        return game.provider === filter
    })

    /**
     * Filtro por Búsqueda
     */
    const filteredBySearch = filteredByPlatform.filter(game => {
        if (search.trim() === "") return true
        return game.name.toLowerCase().includes(search.toLowerCase())
    })

    /**
     * Esta función ordena la lista filtrada de juegos en función de la variable sortBy definida más arriba
     */
    const sortedGames = [...filteredBySearch].sort((a, b) => {
        if (sortBy === "name"){
            return a.name.localeCompare(b.name) // Orden por Nombre del juego
        } else if (sortBy === "date") {
            return new Date(b.release_date) - new Date(a.release_date) // Orden por fecha de publicación
        }
        return 0
    })

    return (<div className="min-h-screen p-6">
            <div className="container mx-auto">
                <h1 className="text-3xl font-bold text-green-200 mb-6 text-center">
                    Mi Biblioteca de Juegos
                </h1>
                
                {message && <MessageDisplay message={message} setMessage={setMessage} />}
                
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
                        <span className="text-green-200">Plataforma:</span>
                        <select 
                            value={filter} 
                            onChange={(e) => setFilter(e.target.value)}
                            className="bg-gray-700 text-white rounded px-3 py-1 hover:cursor-pointer hover:bg-gray-600"
                        >
                            <option value="all">Todas</option>
                            <option value="steam">Steam</option>
                            <option value="epic">Epic Games</option>
                            <option value="gog">GOG</option>
                        </select>
                    </div>
                    
                    <div className="flex items-center gap-2">
                        <span className="text-green-200">Ordenar por:</span>
                        <select 
                            value={sortBy} 
                            onChange={(e) => setSortBy(e.target.value)}
                            className="bg-gray-700 text-white rounded px-3 py-1 hover:cursor-pointer hover:bg-gray-600"
                        >
                            <option value="name">Nombre (A-Z)</option>
                            <option value="date">Fecha (más reciente)</option>
                        </select>
                    </div>
                    
                    <div className="text-green-200">
                        Total: {sortedGames.length} juegos
                    </div>

                    <div className="flex flex-col text-green-200 items-center">
                        <p>¿Quieres añadir un juego?, haz click aquí:</p>
                        <button className="bg-green-500 text-green-50 italic font-bold rounded-2xl w-fit m-5 py-2 px-3 border-2 border-green-100 hover:opacity-50 hover:cursor-pointer" 
                            onClick={()=>navigate('/game-form')}>Añadir Juegos</button> 
                    </div>
                </div>
                
                {/* Lista de juegos */}
                {isLoading ? (
                    <div className="text-center text-green-200 py-20">
                        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-400 mx-auto mb-4"></div>
                        Cargando juegos...
                    </div>
                ) : sortedGames.length === 0 ? (
                    <div className="text-center text-green-200 py-20">
                        <p className="text-xl">No hay juegos en tu biblioteca</p>
                        <p className="mt-2">
                            {search ? `No se encontraron juegos que coincidan con "${search}"` : "Sincroniza tus cuentas o añade juegos manualmente"}
                        </p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                        {sortedGames.map((game) => (
                            <Game key={`${game.id}-${game.provider}`} game={game} />
                        ))}
                    </div>
                )}
            </div>
        </div>)
}
export default MyLibrary