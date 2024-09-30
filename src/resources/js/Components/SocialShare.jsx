// components/SocialShare.js
import {
    FaTelegramPlane,
    FaFacebook,
    FaTwitter,
    FaInstagram,
} from "react-icons/fa";

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

            {/* Facebook Share */}
            <a
                href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}&quote=${encodeURIComponent(shareText)}`}
                // href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(
                //     shareUrl
                // )}&quote=${encodeURIComponent("My passport is ready!")}`}
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
                <FaTwitter className="text-blue-400 w-5 h-5 hover:text-blue-600" />
            </a>

            {/* Instagram Share */}
            <a
                href="https://www.instagram.com/"
                target="_blank"
                rel="noopener noreferrer"
                title="Share this on Instagram!"
            >
                <FaInstagram className="text-pink-600 w-5 h-5 hover:text-pink-800" />
            </a>
        </div>
    );
}

export default SocialShare;
