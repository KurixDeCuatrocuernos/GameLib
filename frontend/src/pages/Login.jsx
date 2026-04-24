import { useEffect, useState } from "react"
import {validateEmail, validatePassword, validateString} from "../utils/validators"

const Login = () => {
    const [typeInputPass, setTypeInputPass] = useState("password")
    const [eyeIcon, setEyeIcon] = useState("/ojo-cruzado.png")
    
    const [userInput, setUserInput] = useState("")
    const [passInput, setPassInput] = useState("")

    const [errorUserInput, setErrorUserInput] = useState("")
    const [errorPassInput, setErrorPassInput] = useState("")
    const [errorGeneral, setErrorGeneral] = useState("")

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

    // Esta función hace la petición al backend para iniciar sesión
    async function login() {
        if (validateEmail(userInput) !== null ||
            validatePassword(passInput) !== null
        ) {
            setErrorUserInput(validateEmail(userInput))
            setErrorPassInput(validatePassword(passInput))

            return
        }
        try {
            const response = await fetch ('/api/functions/userFunctions/logIn.php',{
                method: 'POST', // Cambiarlo a POST
                credentials: 'include', // Para usar la sesión de PHP
                headers: {
                    'Content-Type':'application/json'
                },
                body: JSON.stringify({
                    username: userInput,
                    password: passInput
                })
            })
            const data = await response.json()
            if (response.ok) {
                window.location.href = "/"
            } else {
                console.error("Error:", data.message)
                setErrorGeneral(data.message)
            }
        } catch (error) {
            console.error("Error de red: ",error)
            setErrorGeneral("No se pudo conectar con el backend")
        }
        

    }

    return(<div className="container mx-auto text-center my-10 border-2 bg-green-950 text-green-50 rounded-2xl">
        <h1 className='text-3xl font-bold my-5'>Login</h1>
        <span className="text-red-500">{errorGeneral}</span>
        <form className="mt-5">
            
            <div className="mx-2 flex flex-row justify-center items-center gap-4 mt-5">
                <label htmlFor="username"
                    className="font-bold">
                    Your Email or Username: </label>
                <input type="text" name="username" placeholder="Example@email.com" maxLength={80}
                    value={userInput} onChange={(e) => setUserInput(e.target.value)}
                    className="bg-green-700 rounded-2xl px-5"/>
            </div>
            <span className=" text-amber-400 ">{errorUserInput}</span>
            
            <div className="flex flex-row justify-center items-center gap-4 mt-5">
                <label htmlFor="password"
                    className="font-bold">
                    Your Password: </label>
                <input type={typeInputPass} name="password" placeholder="My_password123" maxLength={20}
                    value={passInput} onChange={(e) => setPassInput(e.target.value)}
                    className="bg-green-700 rounded-2xl px-5" />
                 <button type="button" onClick={changePass} className="w-7 rounded-2xl bg-green-400 hover:cursor-pointer hover:bg-green-900">
                    <img src={eyeIcon} alt="Icono de Ojo" /></button>
            </div>
            <span className=" text-amber-400 ">{errorPassInput}</span>

            
            <div className="mb-10 mt-5 flex flex-row justify-center items-center gap-4">
                <button type="button" onClick={clearForm} 
                    className="bg-red-500 font-bold py-2 px-4 rounded-3xl hover:bg-red-900 hover:cursor-pointer">
                    Cancel</button>
                <button type="button" onClick={login}
                    className="bg-green-400 text-black font-bold py-2 px-4 rounded-3xl hover:bg-red-900 hover:cursor-pointer">
                    Accept</button>
            </div>
            
        </form>
    </div>)
}

export default Login