/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/js/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'border-default': 'var(--surface-border)'
      },
      borderColor: theme => ({
        ...theme('colors'),
        DEFAULT: theme('colors.border-default', 'currentColor')
      })
    },
  },
  plugins: [],
}
