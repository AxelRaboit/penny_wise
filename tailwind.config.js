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
      fontSize: {
        xxs: '0.625rem',
      },
      screens: {
        'xxl': '1740px',
      },
      zIndex: {
        '99': '99',
      },
      colors: {
        primary: {
          DEFAULT: '#010409',
          hover: '#11151A',
          ring: '#2C333A',
          dark: 'rgba(1, 4, 9, 0.1)',
        },
        secondary: {
          DEFAULT: '#0D1117',
          hover: '#1A2028',
          ring: '#2C313A',
          dark: 'rgba(13, 17, 23, 0.1)',
        },
        tertiary: {
          DEFAULT: '#151B23',
          hover: '#202833',
          ring: '#333A44',
          dark: 'rgba(21, 27, 35, 0.1)',
        },
        quaternary: {
          DEFAULT: '#262C36',
          hover: '#3B414D',
          ring: '#4A515E',
          dark: 'rgba(38, 44, 54, 0.1)',
        },
        quinary: {
          DEFAULT: '#31363F',
          hover: '#4A4F58',
          ring: '#4A515E',
          dark: 'rgba(49, 54, 63, 0.1)',
        },
        senary: {
          DEFAULT: '#9198A0',
          hover: '#A4AAB0',
          ring: '#B4BAC0',
          dark: 'rgba(145, 152, 160, 0.1)',
        },
        septenary: {
          DEFAULT: '#f0f6fc',
          hover: '#d9e2e9',
          ring: '#bcd4e2',
          dark: 'rgba(240, 246, 252, 0.1)',
        },
        'accent-primary': {
          DEFAULT: '#4CAF50',
          hover: '#43A047',
          ring: '#388E3C',
          dark: 'rgba(76, 175, 80, 0.1)',
        },
        'accent-secondary': {
          DEFAULT: '#1877F2',
          hover: '#165EAB',
          ring: '#144F8C',
          dark: 'rgba(24, 119, 242, 0.1)',
        },
        'accent-tertiary': {
          DEFAULT: '#6e5494',
          hover: '#5b4378',
          ring: '#4d3766',
          dark: 'rgba(110, 84, 148, 0.1)',
        },
        success: {
          DEFAULT: '#4CAF50',
          hover: '#43A047',
          ring: '#388E3C',
          dark: 'rgba(76, 175, 80, 0.1)',
        },
        danger: {
          DEFAULT: '#F44336',
          hover: '#C62828',
          ring: '#D32F2F',
          dark: 'rgba(244, 67, 54, 0.1)',
        },
        warning: {
          DEFAULT: '#FF9800',
          hover: '#FB8C00',
          ring: '#EF6C00',
          dark: 'rgba(255, 152, 0, 0.1)',
        },
        neutral: {
          DEFAULT: '#9E9E9E',
          hover: '#7F7F7F',
          ring: '#616161',
          dark: 'rgba(158, 158, 158, 0.1)',
        }
      }
    }
  },
  plugins: [],
}
