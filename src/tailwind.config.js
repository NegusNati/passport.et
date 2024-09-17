import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.jsx",
    ],
    darkMode: "class", // Enable class-based dark mode
    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },

        },
    },

    plugins: [
        
        forms,
        require("@tailwindcss/aspect-ratio"),
        function ({ addUtilities }) {
            const newUtilities = {
                ".fake-dark-mode": {
                    transition: "all 3s ease-in-out",
                },
            };
            addUtilities(newUtilities, ["responsive", "hover"]);
        },

    ],
};
