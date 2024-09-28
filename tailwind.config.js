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
          DEFAULT: '#0E0E10',
          hover: '#1C1C22',
          ring: '#2A2A33'
        },
        quinary: {
          DEFAULT: '#1F1F23',
          hover: '#3A3A42',
          ring: '#4B4B56'
        },

        accent: {
          DEFAULT: '#ECEFF4',
          hover: '#DDE1E9',
          ring: '#C6CCD4'
        },
        success: {
          DEFAULT: '#10B981',
          hover: '#22C78A',
          ring: '#0D9466'
        },
        danger: {
          DEFAULT: '#DC2626',
          hover: '#EF4444',
          ring: '#B91C1C'
        },
        warning: {
          DEFAULT: '#D97706',
          hover: '#F59E0B',
          ring: '#B45309'
        }
      }
    }
  },
  plugins: [],
}

