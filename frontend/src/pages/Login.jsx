import { useEffect, useState } from "react"
import {validateUserInput, validatePassword} from "../utils/validators"
import { useAuth } from '../contexts/AuthContext'
import { useNavigate } from "react-router-dom"

const Login = () => {
    const [typeInputPass, setTypeInputPass] = useState("password")
    const [eyeIcon, setEyeIcon] = useState("/ojo-cruzado.png")
    
    const [userInput, setUserInput] = useState("")
    const [passInput, setPassInput] = useState("")

    const [errorUserInput, setErrorUserInput] = useState("")
    const [errorPassInput, setErrorPassInput] = useState("")
    const [errorGeneral, setErrorGeneral] = useState("")

    const { login } = useAuth()
    const [isLoading, setIsLoading] = useState(false)

    const navigate = useNavigate() // variable para redirigir


    // Esta función cambia el tipo del input para ver la contraseña
    function changePass() {
        if (typeInputPass === "password") {
            setTypeInputPass("text")
            setEyeIcon("/ojo.png")
        } else {
            setTypeInputPass("password")
            setEyeIcon("/ojo-cruzado.png")
        }
    }

    // Esta función vacía el formulario y los avisos
    function clearForm() {
        setUserInput("")
        setPassInput("")
        setErrorGeneral("")
        setErrorPassInput("")
        setErrorUserInput("")
    }

    /**
     * Esta función lleva a cabo la revisión de los inputs y realiza el proceso de inicio de sesión mediante el contexto
     */
    async function doLogin() {
        const emailError = validateUserInput(userInput)
        const passError = validatePassword(passInput)
        setErrorUserInput(emailError || "")
        setErrorPassInput(passError || "")

        if (emailError || passError) return

        setIsLoading(true)
        const result = await login(userInput, passInput)
        setIsLoading(false)
        
        if (result.success) {
            window.location.href = "/"
        } else {
            setErrorGeneral(result.error)
        }
    }

    return(<div className="container mx-5 w-auto text-center my-10 border-2 bg-green-950 text-green-50 rounded-2xl">
        <h1 className='text-3xl font-bold my-5'>Login</h1>
        <span className="text-red-500">{errorGeneral}</span>
        <form className="mt-5">
            
            <div className="mx-2 flex flex-row justify-center items-center gap-4 mt-5">
                <label htmlFor="username"
                    className="font-bold">
                    Your Email or Username: </label>
                <input type="text" name="username" placeholder="Username or Email" maxLength={80}
                    value={userInput} onChange={(e) => setUserInput(e.target.value)} disabled={isLoading}
                    className="bg-green-700 rounded-2xl px-5"/>
            </div>
            <span className=" text-amber-400 ">{errorUserInput}</span>
            
            <div className="flex flex-row justify-center items-center gap-4 mt-5">
                <label htmlFor="password"
                    className="font-bold">
                    Your Password: </label>
                <input type={typeInputPass} name="password" placeholder="My_password123" maxLength={20}
                    value={passInput} onChange={(e) => setPassInput(e.target.value)} disabled={isLoading}
                    className="bg-green-700 rounded-2xl px-5" />
                 <button type="button" onClick={changePass} className="w-7 rounded-2xl bg-green-400 hover:cursor-pointer hover:bg-green-900">
                    <img src={eyeIcon} alt="Icono de Ojo" disabled={isLoading} /></button>
            </div>
            <span className=" text-amber-400 ">{errorPassInput}</span>

            
            <div className="mb-10 mt-5 flex flex-row justify-center items-center gap-4">
                <button type="button" onClick={clearForm}  disabled={isLoading}
                    className="bg-red-500 font-bold py-2 px-4 rounded-3xl hover:bg-red-900 hover:cursor-pointer">
                    Cancel</button>
                <button type="button" onClick={doLogin} disabled={isLoading}
                    className="bg-green-400 text-black font-bold py-2 px-4 rounded-3xl hover:bg-red-900 hover:cursor-pointer">
                    {isLoading ? "Cargando...":"Accept"}</button>
            </div>
            
        </form>

        <button className="my-5 text-2xl hover:text-blue-700 hover:cursor-pointer" 
            onClick={() => navigate('/signup')}>
            You haven't got an account? Sign up here!
        </button>

    </div>)
}

export default Login