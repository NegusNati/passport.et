import React, { useState, useEffect } from 'react';
import { usePage, useForm } from '@inertiajs/react';

const LanguageSwitcher = () => {
    const { props } = usePage();
    const { locale } = props;
    const [isOpen, setIsOpen] = useState(false);
    const [currentLanguage, setCurrentLanguage] = useState('English');
    const { data, setData, post, processing, errors, reset } = useForm({
        requestNumber: "",
        firstName: "",
        middleName: "",
        lastName: "",
    });
    const languages = [
        { code: 'en', name: 'English' },
        { code: 'am', name: 'Amharic' },
        { code: 'om', name: 'Oromo' },
        { code: 'ti', name: 'Tigrinya' },
    ];

    useEffect(() => {
        const savedLocale = localStorage.getItem('locale');
        if (savedLocale) {
            const lang = languages.find((lang) => lang.code === savedLocale);
            if (lang) {
                setCurrentLanguage(lang.name);
            }
        } else {
            const lang = languages.find((lang) => lang.code === locale);
            if (lang) {
                setCurrentLanguage(lang.name);
            } else {
                setCurrentLanguage('English');
            }
        }
    }, [locale]);

    const handleLanguageChange = (code) => {
        post(route('change-locale'), { locale: code });
        setIsOpen(false); // Close the dropdown immediately after posting
    };

    const toggleDropdown = () => {
        setIsOpen(!isOpen);
    };

    return (
        <div className="relative inline-block">
            <button
                type="button"
                className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150"
                onClick={toggleDropdown}
            >
                {currentLanguage}
                <svg
                    className="ml-2 -mr-0.5 h-4 w-4"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fillRule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clipRule="evenodd"
                    />
                </svg>
            </button>
            {isOpen && (
                <div className="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                    <div
                        className="py-1"
                        role="menu"
                        aria-orientation="vertical"
                        aria-labelledby="options-menu"
                    >
                        {languages.map((lang) => (
                            <button
                                key={lang.code}
                                onClick={() => handleLanguageChange(lang.code)}
                                className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 w-full text-left"
                                role="menuitem"
                            >
                                {lang.name}
                            </button>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

export default LanguageSwitcher;
