import React, { useState, useEffect } from 'react';

const ThemeSelector = () => {
    const [theme, setTheme] = useState('system'); // Default to system

    useEffect(() => {
        const savedTheme = localStorage.getItem('theme') || 'system';
        setTheme(savedTheme);
        applyTheme(savedTheme);
    }, []);

    const applyTheme = (newTheme) => {
        document.documentElement.classList.add('transition-all', 'duration-500');

        if (newTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (newTheme === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.add('system');
            }
        }
        localStorage.setItem('theme', newTheme);
    };

    const handleThemeChange = (newTheme) => {
        setTheme(newTheme);
        applyTheme(newTheme);
    };

    return (
        <div className="py-1 bg-white dark:bg-gray-700">
            <div
                className="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out cursor-pointer"
                onClick={() => handleThemeChange('light')} 
            >
                Light Mode
            </div>
            <div
                className="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out cursor-pointer"
                onClick={() => handleThemeChange('dark')}
            >
                Dark Mode
            </div>
            <div
                className="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out cursor-pointer"
                onClick={() => handleThemeChange('system')}
            >
                System
            </div>
        </div>
    );
};

export default ThemeSelector;
