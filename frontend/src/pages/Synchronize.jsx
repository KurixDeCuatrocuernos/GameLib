import { useEffect, useState } from "react"
import { useNavigate, useSearchParams } from "react-router-dom"
import { useAuth } from '../contexts/AuthContext'
import GogLinkGuide from "../components/GogLinkGuide"
import GogSynchronization from "../components/GogSynchronization"
import MessageDisplay from "../components/MessageDisplay"

const Synchronize = () => {
    const navigate = useNavigate()

    const [searchParams] = useSearchParams()
    const [message, setMessage] = useState(null)
    const [syncSteam, setSyncSteam] = useState(false)
    const [syncGog, setSyncGog] = useState(false)
    const [isLoading, setIsLoading] = useState(false)
    const [steamLinked, setSteamLinked] = useState(false)
    const [syncResult, setSyncResult] = useState(null)
    
    const { user } = useAuth()

    // Verificar si el usuario ya tiene Steam vinculado
    useEffect(() => {
        const checkSteamStatus = async () => {
            try {
                const response = await fetch('/api/functions/apiFunctions/steam/checkSteamStatus.php', {
                    credentials: 'include'
                });
                const data = await response.json();
                setSteamLinked(data.linked);
            } catch (error) {
                console.error("Error checking Steam status:", error);
            }
        };
        
        if (user) {
            checkSteamStatus();
        }
    }, [user]);

    useEffect(() => {
        const steamSuccess = searchParams.get('steam_success')
        const steamError = searchParams.get('steam_error')
        
        if (steamSuccess === 'true') {
            setMessage({ type: 'success', text: "✅ Successfully linked Steam account!" })
            setSteamLinked(true); // Actualizar estado
            window.history.replaceState({}, '', '/synchronize')
        }
        
        if (steamError) {
            const errorMessages = {
                'connection_failed': '❌ Connection error to Steam. Try again later.',
                'invalid_response': '❌ Steam Authentication error. Please try again.',
                'no_steam_id': "❌ Couldn't get your Steam ID. Did you log in properly?",
                'database_error': '❌ Error saving the account. Try again.'
            }
            
            const errorText = errorMessages[steamError] || "❌ Unknown error linking your Steam library."
            setMessage({ type: 'error', text: errorText })
            window.history.replaceState({}, '', '/synchronize')
        }
    }, [searchParams])

    // 🔥 Función para sincronizar juegos (sin vincular cuenta)
    async function syncSteamGames() {
        setIsLoading(true)
        setSyncResult(null)
        
        try {
            const response = await fetch('/api/functions/apiFunctions/steam/getSteamLib.php', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            
            const result = await response.json()
            setSyncResult(result)
            
            if (result.message === "Biblioteca de Steam Sincronizada") {
                setMessage({ type: 'success', text: `✅ Sincronizado: ${result.inserted} juegos añadidos, ${result.failed} fallidos` })
            } else {
                setMessage({ type: 'error', text: result.message })
            }
        } catch (error) {
            console.error("Error syncing games:", error)
            setMessage({ type: 'error', text: "Error al sincronizar los juegos" })
        } finally {
            setIsLoading(false)
        }
    }

    // 🔥 Función para vincular cuenta y luego sincronizar
    function linkAndSyncSteam() {
        setSyncSteam(true)
        window.location.href = '/api/functions/apiFunctions/steam/steamLogin.php'
    }

    // Pantalla principal: mostrar opciones
    if (!syncGog && !syncSteam) return (
        <div className="bg-gray-900 border-2 border-green-100 w-auto m-5 rounded-3xl p-10 flex flex-col gap-10 justify-evenly items-center">
            <MessageDisplay message={message} setMessage={setMessage}/> {/* Mensaje de error o confirmación */}
            
            {/* Botón Steam */}
            <div className="flex flex-col items-center gap-3">
                <button className="flex flex-row gap-5 items-center hover:bg-gray-800 hover:cursor-pointer rounded-2xl p-5"
                    onClick={steamLinked ? syncSteamGames : linkAndSyncSteam}>
                    <img src="/steam_icon_light.png" alt="Icono de Steam" className="w-20 bg-black border-white border-2 rounded-3xl"/>
                    <div className="text-left">
                        <h3 className="font-semibold text-3xl">Link Steam's Library</h3>
                        {steamLinked && <p className="text-sm text-green-400">✓ Cuenta vinculada</p>}
                    </div>
                </button>
                {isLoading && (
                    <div className="flex items-center gap-2 text-green-400">
                        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-green-400"></div>
                        <span>Sincronizando juegos...</span>
                    </div>
                )}
                {syncResult && (
                    <div className="text-sm text-gray-300 mt-2">
                        <p>Juegos sincronizados: {syncResult.inserted || 0}</p>
                        {syncResult.failed > 0 && <p className="text-red-400">Fallidos: {syncResult.failed}</p>}
                    </div>
                )}
            </div>
            
            {/* Botón GOG */}
            <button className="flex flex-row gap-5 items-center hover:bg-gray-800 hover:cursor-pointer rounded-2xl p-5"
                onClick={() => setSyncGog(true)}>
                <img src="/gog_icon_light.png" alt="Icono de GOG" className="w-20 bg-black border-white border-2 rounded-3xl"/>
                <h3 className="font-semibold text-3xl">Link By GOG Galaxy's CSV File</h3>
            </button>

            <button className="flex flex-row gap-5 items-center hover:bg-gray-800 hover:cursor-pointer rounded-2xl p-5"
            onClick={()=> navigate('/game-form')}>
                <img src="/form_purple_icon.png" alt="Icono de Formulario" className="w-20 bg-black border-white border-2 rounded-3xl"/>
                <h3 className="font-semibold text-3xl">Add your games manually</h3>
            </button>
        </div>
    )

    // Pantalla de vinculación de Steam (sin juegos)
    if (!syncGog && syncSteam) return(
        <div className="bg-gray-900 border-2 border-green-100 w-auto m-5 rounded-3xl p-10 flex flex-col gap-10 justify-evenly items-center">
            <button type="button" onClick={() => setSyncSteam(false)} className="hover:cursor-pointer text-2xl self-start">
                ↩️ Back
            </button>
            <MessageDisplay message={message} setMessage={setMessage}/> {/* Mensaje de error o confirmación */}
            <h1 className="text-3xl font-bold">Linking Steam's Library</h1>
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-400"></div>
            <p className="text-gray-300">Redirecting to Steam...</p>
        </div>
    )

    // Pantalla de vinculación de GOG
    if (!syncSteam && syncGog) return (<>
        <GogSynchronization setSyncGog={setSyncGog}/>
        <GogLinkGuide />
    </>)
}

export default Synchronize