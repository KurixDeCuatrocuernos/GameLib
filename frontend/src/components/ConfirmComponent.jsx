import { useEffect, useState } from "react"

const ConfirmComponent = ({ 
    show, // Mostrar o no el confirm
    setShowConfirmModal, // Mostrar o no el confirm
    title, // Título del confirm
    question, // Pregunta
    extraInformation, // Información relevante
    confirmAction = "Confirmar", // Por defecto Confirmar
    cancelAction = "Cancelar", // Por defecto Cancelar
    type,
    onConfirm // Confirmar
}) => {

    const [titleStyle, setTitleStyle] = useState("")
    const [buttonStyle, setButtonStyle] = useState("") 

    useEffect(() => {
        if (type === 'danger') {
            setTitleStyle("text-xl font-bold mb-4 text-red-400")
            setButtonStyle("bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 hover:cursor-pointer")
        } else if (type === 'success') {
            setTitleStyle("text-xl font-bold mb-4 text-green-400")
            setButtonStyle("bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 hover:cursor-pointer")
        } else if (type === 'warning') {
            setTitleStyle("text-xl font-bold mb-4 text-yellow-400")
            setButtonStyle("bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 hover:cursor-pointer")
        }
    },[type])

    if (!show) return null

    return (
        <div className="fixed inset-0 bg-black/50 flex justify-center items-center z-50">
            <div className="bg-gray-800 rounded-2xl p-6 max-w-md mx-4">
                {title && <h3 className={titleStyle}>{title}</h3>}
                {question && (
                    <p className="text-gray-300 mb-2" dangerouslySetInnerHTML={{ __html: question }} />
                )}
                {extraInformation && (
                    <p className="text-yellow-400 text-sm mb-4" dangerouslySetInnerHTML={{ __html: extraInformation }} />
                )}
                <div className="flex justify-end gap-3 mt-4">
                    <button 
                        onClick={() => setShowConfirmModal(false)}
                        className="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 hover:cursor-pointer"
                    >
                        {cancelAction}
                    </button>
                    <button 
                        onClick={onConfirm}
                        className={buttonStyle}
                    >
                        {confirmAction}
                    </button>
                </div>
            </div>
        </div>
    )
}

export default ConfirmComponent