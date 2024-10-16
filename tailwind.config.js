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
          DEFAULT: '#010409',   // Noir très sombre, proche du noir pur
          hover: '#11151A',     // Gris très foncé pour le hover
          ring: '#2C333A'       // Gris foncé pour les anneaux de focus
        },
        secondary: {
          DEFAULT: '#0D1117',   // Gris-noir pour les sections secondaires
          hover: '#1A2028',     // Gris plus clair pour le hover
          ring: '#2C313A'       // Anneau gris foncé
        },
        tertiary: {
          DEFAULT: '#151B23',   // Noir légèrement cassé
          hover: '#202833',     // Légèrement plus clair pour le hover
          ring: '#333A44'       // Anneau gris encore plus clair
        },
        quaternary: {
          DEFAULT: '#262C36',   // Gris foncé pour accents ou dividers
          hover: '#3B414D',     // Gris plus clair pour le hover
          ring: '#4A515E'       // Anneau de focus gris foncé
        },
        quinary: {
          DEFAULT: '#31363F',   // Couleur intermédiaire entre gris et bleu nuit
          hover: '#4A4F58',     // Gris plus clair pour les hover
          ring: '#555B66'       // Anneau plus clair pour focus
        },
        senary: {
          DEFAULT: '#9198A0',   // Gris doux pour les éléments passifs
          hover: '#A4AAB0',     // Plus clair pour les hover
          ring: '#B4BAC0'       // Anneau pour les focus
        },
        septenary: {
          DEFAULT: '#f0f6fc',   // Très clair, utilisé pour les textes sur des fonds sombres
          hover: '#d9e2e9',     // Légèrement plus foncé pour les hover
          ring: '#bcd4e2'       // Anneau bleu très clair pour le focus
        },
        'accent-primary': {
          DEFAULT: '#4CAF50',   // Vert subtil pour les succès
          hover: '#43A047',     // Vert plus foncé pour le hover
          ring: '#388E3C'       // Anneau vert pour le focus succès
        },
        'accent-secondary': {
          DEFAULT: '#00BCD4',   // Bleu subtil pour les succès
          hover: '#00ACC1',     // Bleu plus foncé pour le hover
          ring: '#0097A7'       // Anneau bleu pour le focus succès
        },
        success: {
          DEFAULT: '#4CAF50',   // Vert subtil pour les succès
          hover: '#43A047',     // Vert plus foncé pour le hover
          ring: '#388E3C'       // Anneau vert pour le focus succès
        },
        danger: {
          DEFAULT: '#F44336',   // Rouge pour les erreurs
          hover: '#C62828',     // Rouge foncé pour le hover
          ring: '#D32F2F'       // Rouge plus vif pour les focus erreurs
        },
        warning: {
          DEFAULT: '#FF9800',   // Orange pour les avertissements
          hover: '#FB8C00',     // Orange foncé pour le hover
          ring: '#EF6C00'       // Orange pour les focus
        },
        neutral: {
          DEFAULT: '#9E9E9E',   // Gris neutre pour les textes secondaires
          hover: '#7F7F7F',     // Gris plus foncé pour les hover
          ring: '#616161'       // Anneau gris neutre pour les focus
        }
      }
    }
  },
  plugins: [],
}
