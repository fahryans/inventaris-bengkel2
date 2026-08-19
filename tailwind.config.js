import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#FF6B35',        // Orange teknik
                secondary: '#FF8C42',
                orange: {
                    50: '#FFF7ED',
                    100: '#FFEDD5',
                    200: '#FED7C4',
                    300: '#FDA49B',
                    400: '#FB7185',
                    500: '#FF6B35',
                    600: '#E85D2F',
                    700: '#DC4A28',
                    800: '#C2361F',
                    900: '#9A281A',
                },
            },
        },
    },

    plugins: [forms],
};
