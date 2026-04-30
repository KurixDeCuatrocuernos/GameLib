import { useState } from "react"
import MessageDisplay from "./MessageDisplay"

const GogSynchronization = ({ setSyncGog }) => {
    const [message, setMessage] = useState(null)
    const [isLoading, setIsLoading] = useState(false)
    const [file, setFile] = useState(null)
    const [abortController, setAbortController] = useState(null) // Variable para controlar el cierre del endpoint del backend por tiempo

    /**
     * Función para almacenar el archivo en la variable file
     * @param {htmlOnchangeEvent} e 
     */
    const handleFileChange = (e) => {
        setFile(e.target.files[0])
    }

    async function handleSubmit (e) {
        e.preventDefault() // Eliminamos el comportamiento habitual
        
        if (!file) {
            setMessage({ type: 'error', text: "Por favor selecciona un archivo CSV"})
            return // Si el archivo no es csv terminamos la función y damos feedback
        }

        setIsLoading(true)

        const controller = new AbortController()
        setAbortController(controller) // Crear controlador para timeout
        
        const timeoutId = setTimeout(() => controller.abort(), 70000) // Timeout de 70 segundos (un poco más que el tiempo de subida)

        try{

            const formData = new FormData()
            formData.append('csv_file', file)

            const response = await fetch('/api/functions/apiFunctions/gog/insertGogLibrary.php', {
                method: 'POST',
                credentials: 'include',
                body: formData,
                signal: controller.signal // Señal para abortar
            })
            const result = await response.json()
            
            if (response.ok) {
                setMessage({
                    type: 'success',
                    text: `${result.message}: ${result.inserted} insertados, ${result.already_exists} existían, ${result.failed} fallidos, ${result.excluded} excluidos.`
                })

                setFile(null)

                e.target.reset()
            
            } else {
                setMessage({ type: 'error', text: result.message || 'Error al procesar el archivo' })
            }

        } catch (error) {
            clearTimeout(timeoutId) // Eliminamos el timeout
            
            if (error.name === 'AbortError') {
                setMessage({ type: 'error', text: "La operación ha excedido el tiempo límite. El archivo es muy grande o hay problemas de conexión." })
            } else {
                console.error("Error: ", error)
                setMessage({ type: 'error', text: 'Error de conexión con el servidor' })
            }

        } finally {
            setIsLoading(false)
            setAbortController(null)
        }

    }

    return (<div className="bg-gray-900 border-2 border-green-100 w-auto m-5 rounded-3xl p-10 flex flex-col gap-10 justify-evenly items-center">
        <button type="button" onClick={() => setSyncGog(false)} className="hover:cursor-pointer text-2xl self-start">
            ↩️ Back
        </button>
        <MessageDisplay message={message} setMessage={setMessage}/> {/* Mensaje de error o confirmación */}
        <h1 className="text-3xl font-bold">Linking GOG's Library</h1>
        <p className="text-gray-300 text-center">
            GOG doesn't offer a public API.<br />
            Please export your library from GOG Galaxy and upload the CSV file.
        </p>
        <form onSubmit={handleSubmit} className="flex flex-col gap-4">
            <input 
                type="file" 
                accept=".csv"
                onChange={handleFileChange}
                disabled={isLoading}
                className="bg-gray-700 text-white p-2 rounded cursor-pointer"
            />
            {!isLoading ? (
                <button 
                    type="submit"
                    disabled = {isLoading}
                    className={isLoading ? "bg-gray-400" : "bg-green-600 text-white font-bold py-2 px-4 rounded hover:bg-green-700 hover:cursor-pointer"}
                >
                Upload CSV File
                </button>
            ) :  (
                <div className="flex items-center gap-2 text-green-400">
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-green-400"></div>
                    <span>Sincronizando juegos...</span>
                </div>
            )}
        </form>
    </div>)
} 
export default GogSynchronization