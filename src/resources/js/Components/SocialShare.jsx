import React from "react";
import { FaTelegramPlane, FaWhatsapp, FaLink } from "react-icons/fa";
import { FaXTwitter } from "react-icons/fa6";

function SocialShare({ shareText, shareUrl }) {
       const handleCopyLink = () => {
           navigator.clipboard.writeText(shareUrl);
       };
    const SocialIcon = ({ href, Icon, name, onClick }) => (
        <a
            href={href}
            onClick={onClick}
            target="_blank"
            rel="noopener noreferrer"
            className="group relative inline-block hover:text-blue-600"
        >
            <Icon className={`w-5 h-5 hover:text-${name.toLowerCase()}-600`} />
            <span className="absolute bottom-full left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none z-10 whitespace-nowrap">
                {name}
            </span>
        </a>
    );

    return (
        <div className="absolute top-4 right-4 flex space-x-2 z-20">
            <SocialIcon
                href="#"
                Icon={FaLink}
                name="Copy Link"
                onClick={(e) => {
                    e.preventDefault();
                    handleCopyLink();
                }}
            />
            <SocialIcon
                href={`https://telegram.me/share/url?url=${encodeURIComponent(
                    shareUrl
                )}&text=${encodeURIComponent(shareText)}`}
                Icon={FaTelegramPlane}
                name="Telegram"
            />
            {/* Facebook Share needs some seo and crawler bugs fixed */}
            {/* <SocialIcon
                href={`https://www.facebook.com/dialog/share?app_id=145634995501895&display=popup&href=${encodeURIComponent(
                    shareUrl
                )}&redirect_uri=https://passport.et`}
                // "https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;src=sdkpreparse"
                Icon={FaFacebook}
                name="Facebook"
            /> */}
            <SocialIcon
                href={`https://api.whatsapp.com/send?text=${encodeURIComponent(
                    shareText
                )}`}
                Icon={FaWhatsapp}
                name="WhatsApp"
            />
            <SocialIcon
                href={`https://twitter.com/intent/tweet?text=${encodeURIComponent(
                    shareText
                )}`}
                Icon={FaXTwitter}
                name="Twitter/X"
            />
            {/* LinkedIn Share needs some seo and crawler bugs fixed */}
            {/* <SocialIcon
                href={`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(
                    shareUrl
                )}`}
                Icon={FaLinkedin}
                name="LinkedIn"
            /> */}
        </div>
    );
}

export default SocialShare;
