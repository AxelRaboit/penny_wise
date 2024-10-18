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
      zIndex: {
        '99': '99',
      },
      colors: {
        primary: {
          DEFAULT: '#010409',   // Very dark black, close to pure black
          hover: '#11151A',     // Very dark gray for hover
          ring: '#2C333A'       // Dark gray for focus rings
        },
        secondary: {
          DEFAULT: '#0D1117',   // Gray-black for secondary sections
          hover: '#1A2028',     // Lighter gray for hover
          ring: '#2C313A'       // Dark gray ring
        },
        tertiary: {
          DEFAULT: '#151B23',   // Slightly off-black
          hover: '#202833',     // Slightly lighter for hover
          ring: '#333A44'       // Lighter gray for focus rings
        },
        quaternary: {
          DEFAULT: '#262C36',   // Dark gray for accents or dividers
          hover: '#3B414D',     // Lighter gray for hover
          ring: '#4A515E'       // Dark gray focus ring
        },
        quinary: {
          DEFAULT: '#31363F',   // Intermediate color between gray and midnight blue
          hover: '#4A4F58',     // Lighter gray for hover
          ring: '#555B66'       // Lighter ring for focus
        },
        senary: {
          DEFAULT: '#9198A0',   // Soft gray for passive elements
          hover: '#A4AAB0',     // Lighter for hover
          ring: '#B4BAC0'       // Ring for focus
        },
        septenary: {
          DEFAULT: '#f0f6fc',   // Very light, used for text on dark backgrounds
          hover: '#d9e2e9',     // Slightly darker for hover
          ring: '#bcd4e2'       // Very light blue ring for focus
        },
        'accent-primary': {
          DEFAULT: '#4CAF50',   // Subtle green for success
          hover: '#43A047',     // Darker green for hover
          ring: '#388E3C'       // Green ring for focus success
        },
        'accent-secondary': {
          DEFAULT: '#1877F2',   // Facebook blue as default
          hover: '#165EAB',     // Darker Facebook blue for hover
          ring: '#144F8C'       // Facebook blue for focus
        },
        success: {
          DEFAULT: '#4CAF50',   // Subtle green for success
          hover: '#43A047',     // Darker green for hover
          ring: '#388E3C'       // Green for focus success
        },
        danger: {
          DEFAULT: '#F44336',   // Red for errors
          hover: '#C62828',     // Dark red for hover
          ring: '#D32F2F'       // Brighter red for focus errors
        },
        warning: {
          DEFAULT: '#FF9800',   // Orange for warnings
          hover: '#FB8C00',     // Darker orange for hover
          ring: '#EF6C00'       // Orange for focus
        },
        neutral: {
          DEFAULT: '#9E9E9E',   // Neutral gray for secondary text
          hover: '#7F7F7F',     // Darker gray for hover
          ring: '#616161'       // Neutral gray ring for focus
        }
      }
    }
  },
  plugins: [],
}
