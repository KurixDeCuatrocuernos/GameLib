export function validateEmail (email) {
    if (!email || email.trim() === ""){
        return "El email está vacío"
    }
    if (typeof email !== "string") {
        return "El email no es un String"
    }
    if (!email.endsWith("@gmail.com")) {
        return "El email no es un email válido"
    } 
    return null
}

export function validateString (value) {
    try{
        if (!value || value.trim() === ""){
            return "El texto está vacío"
        }
        if (typeof value !== "string") {
            return "El texto no es un String"
        }
        return null
    } catch (e) {
        return e
    }
}

export function validatePassword(pass) {
    return null // Por ahora no se evalúa
}



