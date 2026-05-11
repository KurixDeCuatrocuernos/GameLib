import { useNavigate } from 'react-router-dom';
import ContentCard from '../components/adminComponents/ContentCard'
const Dashboard = () => {

    const navigate = useNavigate()

    return (<div className='flex flex-col justify-center items-center m-10 gap-6 w-auto'>
        <button className="flex flex-col justify-center items-center bg-gray-900 text-white rounded-2xl w-auto min-h-80 border-2 border-green-100 transition-all duration-300 hover:scale-105 hover:cursor-pointer"
            onClick={()=>navigate('/')}>
            <h1 className="text-gray-200 text-4xl text-center font-extrabold my-5 mx-5">
                {'Game Library'}
            </h1>
            <h2 className="italic text-gray-300 text-xl text-justify mx-10 my-5">
                {'Una plataforma para unificar todos tus juegos en una biblioteca organizada y fácil de buscar.'}
            </h2>
        </button>
        <button className="flex flex-col justify-center items-center bg-gray-900 text-white rounded-2xl w-auto min-h-80 border-2 border-green-100 transition-all duration-300 hover:scale-105 hover:cursor-pointer"
            onClick={()=>navigate('/library')}>
            <h1 className="text-gray-200 text-4xl text-center font-extrabold my-5 mx-5">
                {'My Library'}
            </h1>
            <h2 className="italic text-gray-300 text-xl text-justify mx-10 my-5">
                {'En nuestra página podrás mantener un seguimiento de tus bibliotecas de Steam, GOG y Epic Games, de forma que sepas claramente dónde se encuentra un juego que ya tengas adquirido. En My Library podrás ver todos los juegos que hayas añadido.'}
            </h2>
        </button>
        <section className="flex flex-col justify-center items-center bg-gray-900 text-white rounded-2xl w-auto min-h-80 border-2 border-green-100 transition-all duration-300 hover:scale-105 hover:cursor-pointer"
            onClick={()=>navigate('/synchronize')}>
            <h1 className="text-gray-200 text-4xl text-center font-extrabold my-5 mx-5">
                {'Sincronización'}
            </h1>
            <h2 className="italic text-gray-300 text-xl text-justify mx-10 my-5">
                {'Facilitamos la labor de sincronización de tu biblioteca de Steam mediante un solo clic, ofrecemos la posibilidad de sincronizar tus juegos mediante un fichero CSV o puedes agregar los juegos manualmente si así lo deseas.'}
            </h2>
        </section>
        <section className="flex flex-col justify-center items-center bg-gray-900 text-white rounded-2xl w-auto min-h-80 border-2 border-green-100 transition-all duration-300 hover:scale-105 hover:cursor-pointer"
            onClick={()=>navigate('/profile')}>
            <h1 className="text-gray-200 text-4xl text-center font-extrabold my-5 mx-5">
                {'Perfil'}
            </h1>
            <h2 className="italic text-gray-300 text-xl text-justify mx-10 my-5">
                {'También puedes configurar tus preferencias de perfil en la página de perfil, y si no estás de acuerdo con nuestras políticas o no estás satisfecho, puedes borrar tu cuenta desde aquí.'}
            </h2>
        </section>
    </div>);
}

export default Dashboard;