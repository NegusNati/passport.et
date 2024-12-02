import React, { useState, useEffect } from "react";
import { motion, AnimatePresence } from "framer-motion";
const DismissibleBanner = ({ text, bgColor }) => {
    const [isVisible, setIsVisible] = useState(true);

    useEffect(() => {
        const isBannerDismissed = sessionStorage.getItem("bannerDismissed");
        if (isBannerDismissed) {
            setIsVisible(false);
        } else {
            const timer = setTimeout(() => {
                handleDismiss();
            }, 10000);

            return () => clearTimeout(timer);
        }
    }, []);

    const handleDismiss = () => {
        setIsVisible(false);
        sessionStorage.setItem("bannerDismissed", "true");
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
        <AnimatePresence>
            {isVisible && (
                <motion.div
                    initial={{ y: -100, opacity: 0 }}
                    animate={{ y: 0, opacity: 1 }}
                    exit={{ y: -100, opacity: 0 }}
                    transition={{ duration: 0.5 }}
                    className={`fixed top-0 left-0 right-0 ${bgColor} text-white p-4 flex justify-between items-center`}
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
                </motion.div>
            )}
        </AnimatePresence>
    );
};

export default DismissibleBanner;
