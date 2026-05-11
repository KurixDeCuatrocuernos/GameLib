import { useState } from "react"

const GogLinkGuide = () => {
    const [sectionStyle, setSectionStyle] = useState("bg-gray-900 border-2 border-green-100 w-auto m-5 rounded-3xl p-10 flex flex-col gap-10 justify-evenly items-start") 
    const [titleStyle, setTitleStyle] = useState("text-4xl font-extrabold")
    const [subtitleStyle, setSubtitleStyle] = useState("text-3xl font-bold text-green-300")
    const [pStyle, setPStyle] = useState("text-xl text-justify")
    const [imgStyle, setImgStyle] = useState("")
    const [strongStyle, setStrongStyle] = useState("text-xl font-bold")
    
    return (
        <main>
            <section className={sectionStyle}>
                <h1 className={titleStyle}>Paso 1</h1>
                <h2 className={subtitleStyle}>Instalar GOG Galaxy</h2>
                <p className={pStyle}>Debes instalar la aplicación de escritorio de GOG Galaxy de <a href="https://www.gog.com/galaxy" className="font-bold italic hover:text-blue-950">la web oficial</a>, y crear una cuenta si no tienes ya una</p>
                {/* <img className={imgStyle} src="" alt="Imagen de la Web de GOG" /> */}
            </section>
            <section className={sectionStyle}>
                <h1 className={titleStyle}>Paso 2</h1>
                <h2 className={subtitleStyle}>Vincular cuentas de Steam y/o Epic Games en la aplicación de GOG Galaxy</h2>
                <p className={pStyle}>Una vez hayas iniciado sesión deberás vincular las cuentas de Steam y/o Epic Games, la aplicación te redirigirá a los inicios de sesión de cada plataforma.</p>
                {/* <img className={imgStyle} src="" alt="Imagen de GOG Galaxy" /> */}
            </section>
            <section className={sectionStyle}>
                <h1 className={titleStyle}>Paso 3</h1>
                <h2 className={subtitleStyle}>Descargar el script GOG-Galaxy-Export-Script</h2>
                <p className={pStyle}>Deberás descargar el programa del <a href="https://github.com/AB1908/GOG-Galaxy-Export-Script" className="font-bold italic hover:text-blue-950">repositorio de Github</a> como .ZIP o bien puedes clonar el repositorio en tu pc mediante Git</p>
                {/* <img className={imgStyle} src="" alt="Imagen del repositorio" /> */}
            </section>
            <section className={sectionStyle}>
                <h1 className={titleStyle}>Paso 4</h1>
                <h2 className={subtitleStyle}>Instalar Python</h2>
                <p className={pStyle}>Deberás descargar Python 3 (o superior) de su <a href="https://www.python.org/downloads/release/python-3144/#:~:text=Files,compressed%20source%20tarball" className="font-bold italic hover:text-blue-950">página oficial</a></p>
                {/* <img className={imgStyle} src="" alt="Imagen de la página de Python" /> */}
                <p className={pStyle}>Importante: Asegúrate de que se instale la librería PIP para el paso siguiente (suele instalarse por defecto)</p>
            </section>
            <section className={sectionStyle}>
                <h1 className={titleStyle}>Paso 4</h1>
                <h2 className={subtitleStyle}>Instalar la librería de Python</h2>
                <p className={pStyle}>Una vez hayas descargado tanto Python comoGOG-Galaxy-Export-Script, si es en un fichero .zip descomprímelo y abre la carpeta del proyecto en un terminal o en PowerShell</p>
                {/* <img className={imgStyle} src="" alt="Imagen de la carpeta de la carpeta del proyecto y el menú de windows con la opción de abrir en Terminal" /> */}
                <p className={pStyle}>Una vez abierto por terminal instala la librería ejecutando el comando: </p>
                <br />
                <strong className={strongStyle}>python -m pip install csv natsort</strong>
                <br />
            </section>
            <section className={sectionStyle}>
                <h1 className={titleStyle}>Paso 5</h1>
                <h2 className={subtitleStyle}>Extraer el fichero CSV</h2>
                <p className={pStyle}>Una vez instalada la librería de Python y en la misma carpeta, ejecuta el comando: </p>
                <br />
                <strong className={strongStyle}>python galaxy_library_export.py</strong>
                <br />
                {/* <img className={imgStyle} src="" alt="Imagen del terminal de Windows 11" /> */}
            </section>
            <section className={sectionStyle}>
                <h1 className={titleStyle}>Último paso</h1>
                <h2 className={subtitleStyle}>Subir el fichero</h2>
                <p className={pStyle}>En la carpeta de windows se habrá generado un fichero llamado gamesDB.csv o algo parecido, ese fichero es el que debes subir a esta página para sincronizar los juegos que tienes</p>
                {/* <img className={imgStyle} src="" alt="Imagen de la página y el sistema de ficheros de Windows 11" /> */}
            </section>
        </main>
    )

}

export default GogLinkGuide