import ContentCard from "../../components/adminComponents/ContentCard";

const AdminDashboard = () => {

    return(<div className="bg-green-950 flex flex-col justify-center items-center my-5">
        <h1 className="text-4xl text-center font-bold my-5">Página de Administrador</h1>
        <h2 className="text-2xl text-center font-semibold italic my-5">Aquí puedes acceder a los diferentes campos de nuestra web</h2>
        {/* Contenedores de las secciones */}
        <section className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 my-10">
            <ContentCard 
                title={"Página de Usuarios"}
                subtitle={"En esta página puedes ver y gestionar la información de los usuarios"}
                buttonText={"Ir a Usuarios"}
                rute={'/admin/users'}  
            />
            <ContentCard 
                title={"Página de Juegos"}
                subtitle={"En esta página puedes ver y gestionar la información de los juegos"}
                buttonText={"Ir a Juegos"}
                rute={'/admin/games'}  
            />
            {/* <ContentCard 
                title={"Página de Proveedores"}
                subtitle={"En esta página puedes ver y gestionar la información de los proveedores de los usuarios"}
                buttonText={"Ir a Proveedores"}
                rute={'/admin/providers'}  
            /> */}
            <ContentCard 
                title={"Página de Juegos de Usuarios"}
                subtitle={"En esta página puedes ver y gestionar la información de los juegos de los usuarios"}
                buttonText={"Ir a Juegos de Usuarios"}
                rute={'/admin/users_games'}  
            />

        </section>
    </div>)
} 
export default AdminDashboard