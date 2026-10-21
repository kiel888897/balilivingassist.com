module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                bla: {
                    50: '#effaf8',
                    100: '#d8f2ed',
                    200: '#b7e2d9',
                    300: '#8bcbbc',
                    400: '#4eaa9a',
                    500: '#128477',
                    600: '#0e6f65',
                    700: '#105951',
                    800: '#0b4c46',
                    900: '#173d46',
                },
            },
            fontFamily: {
                sans: ['DM Sans', 'sans-serif'],
                display: ['Manrope', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
