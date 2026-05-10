/**
 * Esta Página hace de intermediario entre las llamadas para obtener el id de Steam y la creación del user_provider con los datos del usuario y el id de steam
 */
import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

const SteamCallback = () => {
    const navigate = useNavigate();
    const [status, setStatus] = useState('Verificando autenticación con Steam...');

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);

        const completeSteamLink = async () => {
            try {
                setStatus('Procesando autenticación con Steam...');
                const response = await fetch('/api/functions/apiFunctions/steam/return.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: params.toString()
                }); // Llamamos a return.php en el backend

                const result = await response.json();

                if (result.success) {
                    setStatus('¡Cuenta de Steam vinculada con éxito! Redirigiendo...'); // Si todo sale bien mostramos una confirmación
                    setTimeout(() => navigate('/synchronize?steam_success=true'), 2000); // pasados 2 segundos redirigimos a Synchronized.jsx
                } else {
                    setStatus(`Error: ${result.message}. Redirigiendo...`); // Si da error mostramos un mensaje de error
                    setTimeout(() => navigate(`/synchronize?steam_error=${result.error}`), 2000); // Pasados 2 segundos redirigimos a Synchornized.jsx con el error
                }
            } catch (error) {
                console.error('Error al procesar autenticación:', error);
                setStatus('Error de conexión. Redirigiendo...'); // Si hay un error durante la conexión lo mostramos
                setTimeout(() => navigate('/synchronize?steam_error=connection_failed'), 2000); // Redirigimos a Synchronized.jsx y mostramos el error
            }
        };

        if (params.toString()) {
            completeSteamLink(); // Si hay parámetros en la url que nos manda Steam realizanos la llamada a return.php
        } else {
            navigate('/synchronize'); // Si no hay parámetros volvemos a Synchronize.jsx
        }
    }, [navigate]);

    return (
        <div className="flex items-center justify-center min-h-screen bg-gray-900">
            <div className="text-center text-white">
                <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-green-400 mx-auto mb-4"></div>
                <p className="text-xl">{status}</p>
            </div>
        </div>
    );
};

export default SteamCallback;