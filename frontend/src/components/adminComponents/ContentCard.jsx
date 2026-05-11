import { useNavigate } from "react-router-dom"

const ContentCard = ({ title, subtitle, buttonText, rute }) => {

    const navigate = useNavigate()

    return(<div className="flex flex-col justify-center items-center bg-gray-900 text-white rounded-2xl w-90 h-80 border-2 border-green-100">
        <h1 className="text-gray-200 text-2xl text-center font-extrabold my-5 mx-5">
            {title}
        </h1>
        <h2 className="text-gray-300 text-xl text-justify mx-10 my-5">
            {subtitle}
        </h2>
        {buttonText && rute ? (
            <button className="bg-green-500 text-gray-100 font-semibold p-2 border-2 border-green-200 rounded-xl hover:cursor-pointer hover:opacity-50"
                onClick={() => navigate(rute)}>
                {buttonText}
            </button>
        ) : ''
        }
    </div>)
}
export default ContentCard