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
          DEFAULT: '#020617', // slate-950
          hover: '#0f172a',   // slate-900
          ring: '#020617'     // slate-950
        },
        secondary: {
          DEFAULT: '#65a30d', // green-600
          hover: '#84cc16',   // green-500
          ring: '#65a30d'     // green-600
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
          DEFAULT: '#ef4444', // red-500
          hover: '#dc2626',   // red-600
          ring: '#ef4444'     // red-500
        },
        warning: {
          DEFAULT: '#f59e0b', // yellow-500
          hover: '#d97706',   // yellow-600
          ring: '#f59e0b'     // yellow-500
        }
      }
    }
  },
  plugins: [],
}
