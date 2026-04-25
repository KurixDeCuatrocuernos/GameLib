import { useEffect, useState } from "react"
import { useSearchParams } from "react-router-dom"

const Synchronize = () => {
    
    const [searchParams] = useSearchParams()
    const [message, setMessage] = useState()
    const [syncSteam, setSyncSteam] = useState(false)
    const [syncGog, setSyncGog] = useState(false)

    useEffect(()=>{
        const steamSuccess = searchParams.get('steam_success')
        const steamError = searchParams.get('steam_error')
        if (steamSuccess === 'true') {
            setMessage({ type: 'success', text: "✅ ¡Successfully Steam's account linked!" })
            window.history.replaceState({}, '', '/synchronize')
        }
         if (steamError) {
            const errorMessages = {
                'connection_failed': '❌ Connection error to Steam. Try again later.',
                'invalid_response': '❌ Steam Authentication error. Please, try again.',
                'no_steam_id': "❌ Couldn't get your Steam's ID. ¿Did you login properly?",
                'database_error': '❌ Error saving the account. Try again.'
            }
            
            const errorText = errorMessages[steamError] || "❌ There was an unknown error linking your Steam's library."
            setMessage({ type: 'error', text: errorText })
            window.history.replaceState({}, '', '/synchronize')
        }
    }, [searchParams])

    function synchronizingSteam() {
        setSyncSteam(true) // Mostramos la página de Steam
        window.location.href = '/api/functions/apiFunctions/steam/steamLogin.php' // Redirigimos al Backend
    }

    const MessageDisplay = () => {
        if (!message) return null
        return (
            <div className={`fixed top-20 left-1/2 transform -translate-x-1/2 p-4 rounded-lg shadow-lg z-50 ${
                message.type === 'success' ? 'bg-green-600' : 'bg-red-600'
            } text-white`}>
                {message.text}
            </div>
        )
    }

    if (!syncGog && !syncSteam) return (
        <div className="bg-gray-900 border-2 border-green-100 w-auto m-5 rounded-3xl p-10 flex flex-col gap-10 justify-evenly items-center">
            <button className="flex flex-row gap-5 items-center hover:bg-gray-800 hover:cursor-pointer rounded-2xl p-5"
                onClick={synchronizingSteam}>
                <img src="/steam_icon_light.png" alt="Icono de Steam" className="w-20 bg-black border-white border-2 rounded-3xl"/>
                <h3 className="font-semibold text-3xl">Link Steam's Library</h3>
            </button>
            <button className="flex flex-row gap-5 items-center hover:bg-gray-800 hover:cursor-pointer rounded-2xl p-5"
                onClick={()=>setSyncGog(true)}>
                <img src="/gog_icon_light.png" alt="Icono de GOG" className="w-20 bg-black border-white border-2 rounded-3xl"/>
                <h3 className="font-semibold text-3xl">Link GOG's library</h3>
            </button>
        </div>
    )

    if (!syncGog && syncSteam) return(
        <div className="bg-gray-900 border-2 border-green-100 w-auto m-5 rounded-3xl p-10 flex flex-col gap-10 justify-evenly items-center">
            <button type="button" onClick={()=>setSyncSteam(false)} className="hover:cursor-pointer">
                ↩️</button>
            <MessageDisplay />
            <h1>Linking Steam's library</h1>
            <h2>Aquí se muestra un círculo de carga</h2>
        </div>
    )

    if (!syncSteam && syncGog) return (
        <div className="bg-gray-900 border-2 border-green-100 w-auto m-5 rounded-3xl p-10 flex flex-col gap-10 justify-evenly items-center">
            <button type="button" onClick={()=>setSyncGog(false)} className="hover:cursor-pointer">
                🔙</button>
            <h1>Linking GOG's library</h1>
        </div>
    )
}

export default Synchronize