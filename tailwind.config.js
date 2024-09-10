/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./vendor/tales-from-a-dev/flowbite-bundle/templates/**/*.html.twig",
    "./node_modules/flowbite/**/*.js",
    "./assets/**/*.js",
    "./assets/**/**/*.js",
    "./templates/**/*.html.twig",
    "./templates/**/**/*.html.twig",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#020617', // slate-950
          hover: '#0f172a',   // slate-900
          ring: '#020617'     // slate-950
        },
        secondary: {
          DEFAULT: '#1e293b', // slate-800
          hover: '#334155',   // slate-700
          ring: '#1e293b'     // slate-800
        },
        accent: {
          DEFAULT: '#ececf1', // Light accent color
          hover: '#e2e2e7',   // Slightly darker light for hover
          ring: '#ececf1'     // Light color for ring
        },
        success: {
          DEFAULT: '#10b981', // green-500
          hover: '#059669',   // green-600
          ring: '#10b981'     // green-500
        },
        danger: {
          DEFAULT: '#dc2626', // red-600
          hover: '#ef4444',   // red-500
          ring: '#dc2626'     // red-600
        },
        warning: {
          DEFAULT: '#f59e0b', // yellow-500
          hover: '#d97706',   // yellow-600
          ring: '#f59e0b'     // yellow-500
        }
      }
    }
  },
  plugins: [
      require('flowbite/plugin')
  ],
}
