import { 
    createContext, 
    useContext, 
    useState, useEffect } 
from "react";

const AuthContext = createContext()

export const useAuth = () => useContext(AuthContext) // Exportamos el contexto

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null)
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        checkSession()
    }, []) // Verificamos la sesión al cargar la aplicación

    /**
     * Esta función revisa si hay una sesión activa y guarda los datos en el contexto
     */
    async function checkSession() {
        try {
            const response = await fetch('/api/functions/userFunctions/getUserSession.php', {
                credentials: 'include'
            })
            if (response.ok) {
                const data = await response.json()
                setUser({ 
                    id: data.userId,
                    username: data.username, 
                    role: data.role 
                }) // Si hay sesión guardamos las variables que usaremos

            } else {
                setUser(null)
            }
        } catch (error) {
            setUser(null) // Si no hay sesión vaciamos el usuario
        } finally {
            setLoading(false) // En cualquier caso cerramos la carga
        }
    }

    /**
     * Esta función inicia la sesión mediante el contexto (para usar en el login)
     * @param {String} username 
     * @param {String} password 
     * @returns {Promise<{success: boolean, error?: string}>}
     */
    async function login(username, password) {
        const response = await fetch('/api/functions/userFunctions/logIn.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify({ 
                username, password 
            })
        })
        const data = await response.json()
        if (response.ok) {
            setUser({ 
                id: data.userId,
                username: data.username, 
                role: data.role 
            })
            return{ success: true }
        } else {
            return {
                success: false, 
                error:data.message
            }
        }
    }

    /**
     * Esta función sirve para cerrar la sesión mediante el contexto
     */
    async function logout() {
        await fetch('/api/functions/userFunctions/logOut.php', {
            method: 'POST',
            credentials: 'include'
        })
        setUser(null)
    }

    return (
        <AuthContext.Provider value={{ user, loading, login, logout, checkSession }}>
            {children}
        </AuthContext.Provider> 
    )// Devolvemos el provider con las variables y funciones para reutilizarlas en nuestro frontend

}