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
          DEFAULT: '#9ca3af', // bg-gray-400
          hover: '#6b7280',   // bg-gray-500
          ring: '#9ca3af'     // bg-gray-400
        },
        secondary: {
          DEFAULT: '#6b7280', // bg-gray-500
          hover: '#4b5563',   // hover:bg-gray-600
          ring: '#6b7280'     // focus:ring-gray-500
        },
        success: {
          DEFAULT: '#10b981', // bg-green-500
          hover: '#059669',   // hover:bg-green-600
          ring: '#10b981'     // focus:ring-green-500
        },
        danger: {
          DEFAULT: '#ef4444', // bg-red-500
          hover: '#dc2626',   // hover:bg-red-600
          ring: '#ef4444'     // focus:ring-red-500
        },
        warning: {
          DEFAULT: '#f59e0b', // bg-yellow-500
          hover: '#d97706',   // hover:bg-yellow-600
          ring: '#f59e0b'     // focus:ring-yellow-500
        }
      }
    }
  },
  plugins: [],
}
