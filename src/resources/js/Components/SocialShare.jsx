// components/SocialShare.js
import {
    FaTelegramPlane,
    FaFacebook,
    FaWhatsapp,
    FaTwitter,
    FaInstagram,
} from "react-icons/fa";
import { FaXTwitter } from "react-icons/fa6";

function SocialShare({ shareText, shareUrl }) {
    return (
        <div className="absolute top-4 right-4 flex space-x-2">
            {/* Telegram Share */}
            <a
                href={`https://telegram.me/share/url?url=${encodeURIComponent(
                    shareUrl
                )}&text=${encodeURIComponent(shareText)}`}
                target="_blank"
                rel="noopener noreferrer"
            >
                <FaTelegramPlane className="text-blue-400 w-5 h-5 hover:text-blue-600" />
            </a>

            {/* Facebook Share (Feed Share instead of Story) */}
            <a
                href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(
                    shareUrl
                )}&quote=${encodeURIComponent(shareText)}`}
                target="_blank"
                rel="noopener noreferrer"
            >
                <FaFacebook className="text-blue-600 w-5 h-5 hover:text-blue-800" />
            </a>

            {/* X (Twitter) Share */}
            <a
                href={`https://twitter.com/intent/tweet?url=${encodeURIComponent(
                    shareUrl
                )}&text=${encodeURIComponent(shareText)}`}
                target="_blank"
                rel="noopener noreferrer"
            >
                <FaXTwitter className="text-blue-400 w-5 h-5 hover:text-blue-600" />
            </a>

            {/* Instagram Share (Manual instruction for user) */}
            <a
                href="#"
                onClick={() => {
                    navigator.clipboard.writeText(shareText);
                    alert(
                        "Text copied! Open Instagram and paste it manually in your story."
                    );
                    window.open("instagram://story-camera", "_blank");
                }}
                title="Copy to clipboard and share on Instagram!"
            >
                <FaInstagram className="text-pink-600 w-5 h-5 hover:text-pink-800" />
            </a>

            {/* WhatsApp Share */}
            <a
                href={`https://api.whatsapp.com/send?text=${encodeURIComponent(
                    shareText + " " + shareUrl
                )}`}
                target="_blank"
                rel="noopener noreferrer"
            >
                <FaWhatsapp className="text-green-500 w-5 h-5 hover:text-green-700" />
            </a>
        </div>
    );
}

export default SocialShare;
