import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    react(), tailwindcss(),
  ],
  server: {
    host: true, // Permite acceder a vite desde fuera, de cara a Ngrok
    port: 5173,
    strictPort: true,
    allowedHosts: [
      'enroll-owl-chewable.ngrok-free.dev',  // Dominio de Ngrok
      '.ngrok-free.dev'   // Permite cualquier subdominio de Ngrok
    ],
    proxy: {
      '/api': {
        target: 'http://localhost:80', changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '/TFG/backend')
      }
      
    }
  }
})