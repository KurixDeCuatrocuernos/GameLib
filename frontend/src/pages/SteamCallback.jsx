// frontend/src/pages/SteamCallback.jsx
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
                });

                const result = await response.json();

                if (result.success) {
                    setStatus('¡Cuenta de Steam vinculada con éxito! Redirigiendo...');
                    setTimeout(() => navigate('/synchronize?steam_success=true'), 2000);
                } else {
                    setStatus(`Error: ${result.message}. Redirigiendo...`);
                    setTimeout(() => navigate(`/synchronize?steam_error=${result.error}`), 2000);
                }
            } catch (error) {
                console.error('Error al procesar autenticación:', error);
                setStatus('Error de conexión. Redirigiendo...');
                setTimeout(() => navigate('/synchronize?steam_error=connection_failed'), 2000);
            }
        };

        if (params.toString()) {
            completeSteamLink();
        } else {
            navigate('/synchronize');
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