
const MessageDisplay = ({ message, setMessage }) => {
    if (!message) return null
    return (
        <div className={`flex flex-col fixed top-20 left-1/2 transform -translate-x-1/2 p-4 rounded-lg shadow-lg z-50 ${
            
            message.type === 'success' ? 'bg-green-600' : 'bg-red-600'
        } text-white`}>
            <button onClick={() => setMessage(null)}
            className="me-2 text-2xl text-black font-extrabold hover:cursor-pointer hover:text-gray-800">X</button>
            {message.text}
        </div>
    )
}
export default MessageDisplay