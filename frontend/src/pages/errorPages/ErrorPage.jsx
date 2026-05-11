import { useState, useEffect } from "react"
import { useNavigate, useSearchParams } from "react-router-dom"

/**
 * Página de error, mostrará el error y permitirá retroceder e ir a la página de inicio
 * @returns 
 */
const ErrorPage = () => {
    const navigate = useNavigate()
    const [searchParams] = useSearchParams()

    const errorNum = searchParams.get('code') || '404' // Recogemos el código de error de la URL y por defecto mostramos el error 404

    // Errores que pueden mostrarse
    const errorConfig = {
        '400': {
            title: 'Error 400',
            subtitle: 'Solicitud incorrecta',
            description: 'La solicitud no pudo ser procesada debido a una sintaxis incorrecta.'
        },
        '401': {
            title: 'Error 401',
            subtitle: 'No autorizado',
            description: 'Necesitas iniciar sesión para acceder a esta página.'
        },
        '403': {
            title: 'Error 403',
            subtitle: 'Acceso denegado',
            description: 'No tienes permiso para acceder a esta página.'
        },
        '404': {
            title: 'Error 404',
            subtitle: 'Página no encontrada',
            description: 'Lo sentimos, pero la página a la que intentas acceder no se ha encontrado en nuestro sistema.'
        },
        '500': {
            title: 'Error 500',
            subtitle: 'Error interno del servidor',
            description: 'Ha ocurrido un error interno en el servidor. Por favor, inténtalo más tarde.'
        },
        '503': {
            title: 'Error 503',
            subtitle: 'Servicio no disponible',
            description: 'El servidor está temporalmente fuera de servicio. Por favor, inténtalo más tarde.'
        }
    }

    const error = errorConfig[errorNum] || errorConfig['404'] // Usar 404 por defecto

    // Usaremos un color diferente según el tipo de error
    const getTitleColor = () => {
        if (errorNum.startsWith('4')) return 'text-red-400'
        if (errorNum.startsWith('5')) return 'text-orange-400'
        return 'text-yellow-400'
    }

    return (
        <div className="flex flex-col justify-center items-center text-white my-30">
            <h1 className={`text-6xl font-bold ${getTitleColor()} mb-4`}>
                {error.title}
            </h1>
            <h2 className="text-2xl mb-8">
                {error.subtitle}
            </h2>
            <p className="text-gray-300 mb-8 text-center max-w-md">
                {error.description}
            </p>
            <div className="flex gap-4">
                <button 
                    onClick={() => navigate(-1)} // Volver a la página anterior
                    className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors hover:cursor-pointer"
                >
                    ← Volver
                </button>
                <button 
                    onClick={() => navigate("/")} // Ir a la página de inicio
                    className="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors hover:cursor-pointer"
                >
                    Ir al inicio
                </button>
            </div>
        </div>
    )
}

export default ErrorPage