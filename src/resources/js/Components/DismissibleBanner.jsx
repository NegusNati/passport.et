import React, { useState, useEffect } from "react";

const DismissibleBanner = ({ text, bgColor }) => {
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const isBannerDismissed = localStorage.getItem("bannerDismissed");
        if (!isBannerDismissed) {
            setIsVisible(true);
        }
    }, []);

    const handleDismiss = () => {
        setIsVisible(false);
        localStorage.setItem("bannerDismissed", "true");
    };

    const options = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    };
    const formattedDate = new Date()
        .toLocaleDateString("en-US", options)
        .replace(",", "/");
    return (
        isVisible && (
            <div
                className={`fixed top-0 left-0 right-0 ${bgColor} text-white p-4 flex justify-between items-center transition-transform duration-300 transform ${
                    isVisible ? "translate-y-0" : "-translate-y-full"
                }`}
                style={{ zIndex: 1000 }}
            >
                <div className="flex-grow"></div>
                <span className="text-center flex-grow">
                    <p> {text}</p>
                    <p className="text-sm font-bold">{`Updated Today, ${formattedDate}`}</p>
                </span>
                <div className="flex-grow flex justify-end">
                    <button
                        onClick={handleDismiss}
                        className="bg-transparent border-0 text-white text-2xl"
                    >
                        &times;
                    </button>
                </div>
            </div>
        )
    );
};

export default DismissibleBanner;
