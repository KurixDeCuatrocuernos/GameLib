import { useEffect, useRef, useState, useImperativeHandle, forwardRef } from "react"

// ForwardRef envuelve el componente para poder vaciar la búsqueda desde fuera del componente mediante useRef
const Navigator = forwardRef(({ onSelectGame, placeholder="Buscar juegos..." }, ref) =>{ 
    const [search, setSearch] = useState("")
    const [games, setGames] = useState([])
    const [loading, setLoading] = useState(false)
    const [showDropdown, setShowDropdown] = useState(false)

    const inputRef = useRef(null)

    useEffect(() => {
        if (search.length > 2) { // Solo buscamos si hay al menos 3 caracteres
            searchGames()
            setShowDropdown(true)
        } else {
            setGames([]) // Limpiamos resultados si la búsqueda es corta
            setShowDropdown(false) // No mostramos el desplegable con los resultados
        }
    },[search])

    /**
     * 
     */
    useImperativeHandle(ref, () => ({
        resetSearch: () => {
            setSearch("")
            setGames([])
            setShowDropdown(false)
            if (inputRef.current) {
                inputRef.current.value = ""
            }
        }
    }))

    async function searchGames() {
        setLoading(true)
        try {
            const response = await fetch(`/api/functions/apiFunctions/igdb/getIgdbSearchGames.php?search=${encodeURIComponent(search)}`, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                }
            }) 
            if (response.ok) {
                const data = await response.json()
                
                // console.log("Respuesta del backend:", data)
                // console.log("message es array?", Array.isArray(data['message']))
                // console.log("Longitud:", data['message']?.length)

                setGames(data['message'] || [])
            } else {
                setGames(["Error en el response"])
            }
        } catch (error) {
            console.log("Error en la conexión con el Backend")
        } finally {
            setLoading(false)
        }
    }

    const handleSelectGame = (game) => {
        setSearch(game.name) // Modificamos Search al nombre del
        setGames([])
        setShowDropdown(false)
        if (onSelectGame) {
            onSelectGame(game) // Si tenemos la propiedad para seleccionar un juego, le pasamos el nombre
        }
        if (inputRef.current) {
            inputRef.current.blur() // Quitamos el foco sobre el input para no mostrar el desplegable
        }
    }

    return (
        <div className="relative">
            <form className="flex flex-row justify-between items-center bg-green-50 text-black rounded-2xl px-2 w-auto hover:cursor-text" 
                onClick={(e)=> {
                    if (e.target.tagName !== 'INPUT') {
                        inputRef.current?.focus()
                    }
                }}
                onSubmit={(e) => e.preventDefault()}>
                <input 
                    type="text" 
                    ref={inputRef}
                    onChange={(e) => setSearch(e.target.value)} 
                    onFocus={(e) => setShowDropdown(true)} // Si el foco está sobre el input mostramos el dropdown
                    onBlur={() => setTimeout(() => {setShowDropdown(false)}, 150)} // Si el foco se va del input no mostramos el foco
                    value={search}
                    className="italic w-full me-5 px-4 py-1 rounded-2xl"
                    placeholder="Buscar juegos..."
                />
                <div className="px-2">🔍</div>
            </form>
            
            {loading && <div className="absolute bg-white text-black p-2 mt-1 rounded shadow-lg">Buscando...</div>}
            
            {showDropdown && !loading && games.length > 0 ? (
                <div className="absolute bg-white text-black rounded shadow-lg mt-1 max-h-60 overflow-y-auto w-full z-10">
                    {games.map((game, index) => (
                        <div key={index} className="p-2 hover:bg-gray-200 cursor-pointer border-b border-gray-300" 
                            onMouseDown={(e) => {
                                e.preventDefault() // Evita que el input pierda el foco
                                handleSelectGame(game)
                            }}
                        >
                            {game.name}
                        </div>
                    ))}
                </div>
            ) : ''}
        </div>
    )
})

export default Navigator