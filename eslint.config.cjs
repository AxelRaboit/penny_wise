const globals = require('globals');
const prettierPlugin = require('eslint-plugin-prettier');

/** @type {import('eslint').Linter.FlatConfig[]} */
module.exports = [
  {
    ignores: [
      'node_modules/**',
      'vendor/**',
      'public/**',
      'assets/vendor/**',
      'webpack.config.js',
      'tailwind.config.js',
    ],
  },
  {
    files: ['**/*.js'],
    plugins: {
      prettier: prettierPlugin,
    },
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: globals.browser,
    },
    rules: {
      semi: 'error',
      'prefer-const': 'error',
      'prettier/prettier': 'error',
    },
  },
];
