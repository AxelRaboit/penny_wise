const { createThemes } = require('tw-colors');
const themes = [
  require('./themes/charcoal'),
  require('./themes/default'),
  require('./themes/forest'),
  require('./themes/graphite'),
  require('./themes/midnight'),
  require('./themes/mystic'),
  require('./themes/slate'),
  require('./themes/dark'),
  require('./themes/dimmed'),
  require('./themes/light')
];

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
        'xs': '480px',
        'sm': '640px',
        'md': '768px',
        'lg': '1024px',
        'xl': '1280px',
        'xxl': '1740px',
      },
      zIndex: {
        '99': '99',
      },
    },
  },
  plugins: [
    createThemes(
        Object.assign({}, ...themes),
        {
          defaultTheme: 'default',
        }
    )
  ],
};
