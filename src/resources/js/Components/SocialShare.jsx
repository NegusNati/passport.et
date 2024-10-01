import React from "react";
import {
    FaTelegramPlane,
    FaFacebook,
    FaWhatsapp,
    FaInstagram,
} from "react-icons/fa";
import { FaXTwitter } from "react-icons/fa6";

function SocialShare({ shareText, shareUrl }) {
    const SocialIcon = ({ href, Icon, name }) => (
        <a
            href={href}
            target="_blank"
            rel="noopener noreferrer"
            className="group relative inline-block"
        >
            <Icon className={`w-5 h-5 hover:text-${name.toLowerCase()}-600`} />
            <span className="absolute bottom-full left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-10 whitespace-nowrap">
                Share to {name}
            </span>
        </a>
    );

    return (
        <div className="absolute top-4 right-4 flex space-x-2 z-20">
            <SocialIcon
                href={`https://telegram.me/share/url?url=${encodeURIComponent(
                    shareUrl
                )}&text=${encodeURIComponent(shareText)}`}
                Icon={FaTelegramPlane}
                name="Telegram"
            />
            <SocialIcon
                href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(
                    shareUrl
                )}&quote=${encodeURIComponent(shareText)}`}
                Icon={FaFacebook}
                name="Facebook"
            />
            <SocialIcon
                href={`https://twitter.com/intent/tweet?text=${encodeURIComponent(
                    shareText
                )}`}
                Icon={FaXTwitter}
                name="Twitter"
            />
            <SocialIcon href="#" Icon={FaInstagram} name="Instagram" />
            <SocialIcon
                href={`https://api.whatsapp.com/send?text=${encodeURIComponent(
                    shareText
                )}`}
                Icon={FaWhatsapp}
                name="WhatsApp"
            />
        </div>
    );
}

export default SocialShare;
