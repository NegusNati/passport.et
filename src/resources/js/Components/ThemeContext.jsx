// ThemeContext.js
import React, { createContext, useContext, useEffect, useState } from "react";

const ThemeContext = createContext();

export const ThemeProvider = ({ children }) => {
    const [theme, setTheme] = useState("system"); // Default to system

    useEffect(() => {
        const savedTheme = localStorage.getItem("theme") || "system";
        setTheme(savedTheme);
        applyTheme(savedTheme);
    }, []);

    const applyTheme = (newTheme) => {
        if (newTheme === "dark") {
            document.documentElement.classList.add("dark", "fake-dark-mode");
        } else if (newTheme === "light") {
            document.documentElement.classList.remove("dark", "fake-dark-mode");
        } else {
            if (window.matchMedia("(prefers-color-scheme: dark)").matches) {
                document.documentElement.classList.add(
                    "dark",
                    "fake-dark-mode"
                );
            } else {
                document.documentElement.classList.remove(
                    "dark",
                    "fake-dark-mode"
                );
            }
        }
        localStorage.setItem("theme", newTheme);
    };

    const handleThemeChange = (newTheme) => {
        setTheme(newTheme);
        applyTheme(newTheme);
    };

    return (
        <ThemeContext.Provider value={{ theme, handleThemeChange }}>
            {children}
        </ThemeContext.Provider>
    );
};

export const useTheme = () => useContext(ThemeContext);
