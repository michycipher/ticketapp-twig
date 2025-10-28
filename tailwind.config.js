/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./templates/**/*.twig", "./src/**/*.js", "./public/**/*.html"],
  theme: {
    extend: {
      fontFamily: {
        montserrat: ['Montserrat', 'sans-serif'], 
      },
    },
  },
  plugins: [],
};


