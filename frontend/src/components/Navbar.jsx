import { useEffect, useState } from "react"


const Navbar = () => {

    const [username, setUserName] = useState("")
    const [userRole, setUserRole] = useState("")

    useEffect(()=> {
        getUserSession()
    },[])


    async function getUserSession() {
        try {
            const response = await fetch('/api/functions/userFunctions/getUserSession.php', {
                method: 'GET', // Cambiarlo a POST
                credentials: 'include', // Para usar la sesión de PHP
                headers: {
                    'Content-Type':'application/json'
                },
            })
            const data = await response.json()
            console.log("Respuesta: ",data)
            if (response.ok) {
                    setUserName(data.username)
                    setUserRole(data.role)
            } else {
                console.log(data.message)
            }
            } catch (error) {
                console.error("Error: ",error)
            }
    }

    return (<header>
        <div >
            <div className="flex flex-row justify-between items-center m-5">
                <div>
                    <a href="/">
                        <img className="w-20 me-5"
                        src="/gamepad-Icon.png" alt="Logo del proyecto" />
                    </a>
                <a className="text-4xl font-bold italic text-green-200" 
                    href="/" >Game Library</a>
                </div>


                {username && userRole ?  
                    <div className="flex flex-row w-30">
                        <div className="flex flex-col">
                            <p className="text-2xl mr-5">{username}</p>
                            <p className="">{userRole}</p>
                        </div>
                        <img src="/gamepad-Icon.png" alt="User's Picture" className="w-15"/>
                    </div>
                : ''}

            </div>
            
        </div>
        <nav className="flex flex-row gap-4 justify-evenly items-center w-full text-2xl font-bold italic">
            <a className=" hover:text-green-950" 
                href="/login">Log In</a>
            <p>/</p>
            <a className=" hover:text-green-950" 
                href="/signup" >Sign Up</a>
            <p>/</p>
            <a className=" hover:text-green-950" 
                href="">Enlace 3</a>
            <p>/</p>
            <a className=" hover:text-green-950" 
                href="">Enlace 4</a>
            <p>/</p>
            <a className=" hover:text-green-950" 
                href="">Enlace 5</a>
        </nav>

    </header>)
}

export default Navbar