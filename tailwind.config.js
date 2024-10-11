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
          hover: '#0F0F11',
          ring: '#34343A'
        },
        secondary: {
          DEFAULT: '#2a2a38',
          hover: '#20202c',
          ring: '#20202c'
        },
        tertiary: {
          DEFAULT: '#F3F4F6',
          hover: '#C6CCD4',
          ring: '#D1D5DB'
        },
        quaternary: {
          DEFAULT: '#0E0E10',
          hover: '#060607',
          ring: '#2A2A33'
        },
        quinary: {
          DEFAULT: '#1F1F23',
          hover: '#151519',
          ring: '#4B4B56'
        },
        senary: {
          DEFAULT: '#1f1f2b',
          hover: '#141421',
          ring: '#181823'
        },
        septenary: {
          DEFAULT: '#9146FF',
          hover: '#6e34cc',
          ring: '#5B29A6'
        },
        accent: {
          DEFAULT: '#ECEFF4',
          hover: '#C6CCD4',
          ring: '#C6CCD4'
        },
        success: {
          DEFAULT: '#10B981',
          hover: '#0D9466',
          ring: '#0D9466'
        },
        danger: {
          DEFAULT: '#DC2626',
          hover: '#A31E1E',
          ring: '#B91C1C'
        },
        warning: {
          DEFAULT: '#D97706',
          hover: '#A35A04',
          ring: '#B45309'
        },
      }
    }
  },
  plugins: [],
}
