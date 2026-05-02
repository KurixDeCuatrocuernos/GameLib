
const Game = ({ game }) => {
    
    const getCoverUrl = (cover) => {
        if (!cover) { return "https://placehold.co/300x450?text=No+Cover" } // Si no tiene cover devolvemos la imagen por defecto de la api placehold.co
        if (cover.startsWith('http')) { return cover } // Si el cover ya empieza por http devolvemos directamente el cover
        return 'https:'+cover // En otro caso, añadimos https: para completar la URL (no debería ocurrir)
    }

    const formatDate = (date) => {
        if (!date) return "fecha desconocida" // Si no hay fecha mostramos un mensaje de error
        return new Date(date).toLocaleDateString('es-ES', {
            year: 'numeric', month: 'long', day:'numeric'
        }) // Formateamos la fecha para mostrarla adecuadamente
    }

    const getProviderName = (provider) => {
        switch(provider) {
            case 'steam': return 'Steam'
            case 'epic': return 'Epic Games'
            case 'gog': return 'GOG'
            default: return 'Unknown'
        }
    }

    const getProviderIcon = (provider) => {
        switch(provider) {
            case 'steam': return 'steam_icon_light.png'
            case 'epic': return 'epic_games_icon.png'
            case 'gog': return 'gog_icon.png'
            default: return 'db-icon.png'
        }
    }

    const getProviderColor = (provider) => {
        switch(provider) {
            case 'steam': return 'bg-black text-white rounded-4xl'
            case 'epic': return 'text-white'
            case 'gog': return 'bg-white rounded-xl '
            default: return 'bg-gray-600 text-white'
        }
    }

    return(<div className="flex flex-col justify-center items-center bg-gray-900 rounded-xl border-2 border-green-100 overflow-hidden hover:shadow-lg hover:shadow-green-500/20 transition-all duration-300 hover:scale-105">
            {/* Portada */}
            <div className="m-5">
                <img 
                    src={getCoverUrl(game.cover)} 
                    alt={game.name}
                    className="object-cover object-center rounded-2xl"
                />
            </div>
            
            {/* Información del juego */}
            <div className="p-4">
                {/* Nombre del juego */}
                <h3 className="text-lg font-bold text-green-100 mb-1 line-clamp-1" title={game.name}>
                    {game.name}
                </h3>
                {/* Fecha de salida del juego */}
                <p className="text-sm text-gray-400">
                    📅 {formatDate(game.release_date)}
                </p>

                {/* Mostrar el Provider */}
                <div className="m-5 top-2 right-2 text-green-200 text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1">
                    <img src={getProviderIcon(game.provider)} alt="Database Icon" className={`w-10 h-10 ${getProviderColor(game.provider)}`} />
                    <span className="">{getProviderName(game.provider)}</span>
                </div>
            </div>
        </div>)
}

export default Game