/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                display: ['Syne', 'sans-serif'],
                sans:    ['DM Sans', 'sans-serif'],
            },
            fontWeight: {
                700: '700',
                800: '800',
            },
            colors: {
                sand: {
                    DEFAULT: '#F7F2EB',
                    dark:    '#EDE5D8',
                },
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.5rem',
            },
            screens: {
                xs: '390px',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
