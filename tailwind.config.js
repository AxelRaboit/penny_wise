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
          DEFAULT: '#10a37f', // Main green color
          hover: '#0e8c6b',   // Darker green for hover
          ring: '#10a37f'     // Green for ring
        },
        secondary: {
          DEFAULT: '#202123', // Main dark background color
          hover: '#2d2e30',   // Slightly lighter dark for hover
          ring: '#202123'     // Dark color for ring
        },
        accent: {
          DEFAULT: '#ececf1', // Light accent color
          hover: '#e2e2e7',   // Slightly darker light for hover
          ring: '#ececf1'     // Light color for ring
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
