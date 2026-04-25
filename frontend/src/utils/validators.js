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

export function validateUserInput(input) {
    if (!input || input.trim() === "") {
        return "El email o nombre de usuario está vacío"
    }
    
    // Si contiene @, validar¡mos como email
    if (input.includes("@")) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(input)) {
            return "El email no tiene un formato válido"
        }
    } else {
        // Validar como username (solo letras, números, guión bajo)
        const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/
        if (!usernameRegex.test(input)) {
            return "El nombre de usuario debe tener 3-20 caracteres (letras, números, _)"
        }
    }
    
    return null
}

export function validateUsername(value) {
    try{
        if (!value || value.trim() === ""){
            return "El nombre de usuario está vacío"
        }
        if (typeof value !== "string") {
            return "El nombre de usuario no es válido"
        }
        const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/
        if (!usernameRegex.test(value)) {
            return "El nombre de usuario debe tener 3-20 caracteres (letras, números, _)"
        }
        return null
    } catch (e) {
        return e
    }
}


