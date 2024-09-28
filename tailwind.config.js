/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./assets/**/**/*.js",
    "./templates/**/*.html.twig",
    "./templates/**/**/*.html.twig",
  ],
  theme: {
    extend: {
      screens: {
        'xxl': '1740px',
      },
      colors: {
        primary: {
          DEFAULT: '#18181B',
          hover: '#28282D',
          ring: '#34343A'
        },
        secondary: {
          DEFAULT: '#8064E7',
          hover: '#9577FF',
          ring: '#6753B6'
        },
        tertiary: {
          DEFAULT: '#F3F4F6',
          hover: '#E5E7EB',
          ring: '#D1D5DB'
        },
        quaternary: {
          DEFAULT: '#1F1F23',
          hover: '#2C2C34',
          ring: '#3B3B44'
        },
        accent: {
          DEFAULT: '#ECEFF4',
          hover: '#DDE1E9',
          ring: '#C6CCD4'
        },
        success: {
          DEFAULT: '#10B981', // Vert pour les succès
          hover: '#22C78A',   // Vert légèrement plus lumineux pour les interactions
          ring: '#0D9466'     // Vert sombre pour les effets de focus
        },
        danger: {
          DEFAULT: '#DC2626', // Rouge vif pour les erreurs
          hover: '#EF4444',   // Rouge encore plus éclatant pour les interactions
          ring: '#B91C1C'     // Rouge sombre pour les effets de focus
        },
        warning: {
          DEFAULT: '#D97706', // Jaune foncé pour les avertissements
          hover: '#F59E0B',   // Jaune plus brillant pour les interactions
          ring: '#B45309'     // Jaune atténué pour les effets de focus
        }
      }
    }
  },
  plugins: [],
}

