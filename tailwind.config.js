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
          DEFAULT: '#1C1C1E', // Very dark grey, perfect for dark backgrounds
          hover: '#2D2D2F',   // A slightly lighter shade for hover states
          ring: '#3C3C3E'     // Dark grey for focus rings
        },
        secondary: {
          DEFAULT: '#2E2E30', // Medium grey, ideal for secondary sections
          hover: '#404042',   // A lighter tone for hover interactions
          ring: '#5A5A5D'     // A softer ring for focus states
        },
        tertiary: {
          DEFAULT: '#E5E5E5', // Very light grey, great for neutral backgrounds
          hover: '#CCCCCC',   // Slight variation for hover on light elements
          ring: '#AFAFAF'     // Accent color for focus rings on light zones
        },
        quaternary: {
          DEFAULT: '#555555', // Grey for accents or dividers
          hover: '#6B6B6B',   // A bit lighter for hover states
          ring: '#787878'     // Ring for accentuated elements
        },
        quinary: {
          DEFAULT: '#A3A3A3', // Pale grey for passive elements or text
          hover: '#8C8C8C',   // Slightly darker tone for hover states
          ring: '#707070'     // Ring for passive elements focus
        },
        accent: {
          DEFAULT: '#F5F5F5', // Almost white, for neutral section backgrounds
          hover: '#E0E0E0',   // Slight variation for hover interactions
          ring: '#D4D4D4'     // Ring for focus on very light elements
        },
        success: {
          DEFAULT: '#4CAF50', // Subtle green for success messages
          hover: '#43A047',   // Darker green for positive interactions
          ring: '#388E3C'     // Focus ring for success states
        },
        danger: {
          DEFAULT: '#F44336', // Subtle red for error states
          hover: '#E53935',   // Darker red for error hover states
          ring: '#D32F2F'     // To highlight error messages or states
        },
        neutral: {
          DEFAULT: '#9E9E9E', // Neutral grey for secondary text or details
          hover: '#7F7F7F',   // Slightly darker tone for hover on neutral elements
          ring: '#616161'     // Accent ring for focus on neutral elements
        }
      }
    }
  },
  plugins: [],
}
