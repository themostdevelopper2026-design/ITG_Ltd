/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./*.html", "./src/js/**/*.js"],
  theme: {
    extend: {
      colors: {
        'itg-blue': '#003882',
        'itg-orange': '#F28C28',
        'itg-lightblue': '#29ABE2',
        'itg-lightbg': '#F5F7FA',
        'itg-palebg': '#EAF2FB',
        'itg-darkblue': '#3A75E0',
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
