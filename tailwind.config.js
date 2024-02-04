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
        screens: {
            'xs': '425px',
            'sm': '640px',
            'md': '768px',
            'lg': '1024px',
            'xl': '1280px',
            '2xl': '1536px',
        }
    },
    plugins: [],
}
