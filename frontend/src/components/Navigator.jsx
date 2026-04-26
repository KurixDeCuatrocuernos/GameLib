import { useEffect, useState } from "react"

const Navigator = () =>{
    const [search, setSearch] = useState("")
    const [games, setGames] = useState([])
    const [loading, setLoading] = useState(false)

    useEffect(() => {
        if (search.length > 2) { // Solo buscamos si hay al menos 3 caracteres
            searchGames()
        } else {
            setGames([]) // Limpiamos resultados si la búsqueda es corta
        }
    },[search])

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

    return (
        <div className="relative">
            <form className="bg-green-50 text-black rounded-2xl px-2 w-auto" onSubmit={(e) => e.preventDefault()}>
                <input 
                    type="text" 
                    onChange={(e) => setSearch(e.target.value)} 
                    value={search}
                    className="italic px-2 py-1 rounded-l-2xl"
                    placeholder="Buscar juegos..."
                />
                <button type="submit" className="px-2">🔍</button>
            </form>
            
            {loading && <div className="absolute bg-white text-black p-2 mt-1 rounded shadow-lg">Buscando...</div>}
            
            {games.length > 0 && !loading && (
                <div className="absolute bg-white text-black rounded shadow-lg mt-1 max-h-60 overflow-y-auto w-full z-10">
                    {games.map((game, index) => (
                        <div key={index} className="p-2 hover:bg-gray-200 cursor-pointer border-b border-gray-300">
                            {game}
                        </div>
                    ))}
                </div>
            )}
        </div>
    )
}

export default Navigator