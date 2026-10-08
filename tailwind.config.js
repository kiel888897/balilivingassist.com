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
                    500: '#128477',
                    600: '#0e6f65',
                    700: '#105951',
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
