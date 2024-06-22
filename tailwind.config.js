/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#3b82f6', // Corresponds to bg-blue-500
          hover: '#2563eb',   // Corresponds to hover:bg-blue-600
          ring: '#3b82f6'     // Corresponds to focus:ring-blue-500
        },
        secondary: {
          DEFAULT: '#6b7280', // Corresponds to bg-gray-500
          hover: '#4b5563',   // Corresponds to hover:bg-gray-600
          ring: '#6b7280'     // Corresponds to focus:ring-gray-500
        },
        success: {
          DEFAULT: '#10b981', // Corresponds to bg-green-500
          hover: '#059669',   // Corresponds to hover:bg-green-600
          ring: '#10b981'     // Corresponds to focus:ring-green-500
        },
        danger: {
          DEFAULT: '#ef4444', // Corresponds to bg-red-500
          hover: '#dc2626',   // Corresponds to hover:bg-red-600
          ring: '#ef4444'     // Corresponds to focus:ring-red-500
        },
        warning: {
          DEFAULT: '#f59e0b', // Corresponds to bg-yellow-500
          hover: '#d97706',   // Corresponds to hover:bg-yellow-600
          ring: '#f59e0b'     // Corresponds to focus:ring-yellow-500
        }
      }
    }
  },
  plugins: [],
}
