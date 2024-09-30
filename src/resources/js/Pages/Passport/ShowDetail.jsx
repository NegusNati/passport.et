import Authenticated from "@/Layouts/AuthenticatedLayout";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import formatDate from "@/helpers/formarDate";
import {
    FaTelegramPlane,
    FaFacebook,
    FaTwitter,
    FaInstagram,
} from "react-icons/fa";

const days = [
    "monday",
    "tuesday",
    "wednesday",
    "thursday",
    "friday",
    "saturday",
    "sunday",
];

function ShowDetail({ auth, passport }) {
    function getDayOfWeek(firstName) {
        const letter = firstName.charAt(0).toLowerCase();
        let days = [];

        if (["a", "b", "c", "d", "e", "f", "g"].includes(letter))
            days.push("Monday");
        if (
            ["m", "n", "o", "p", "q", "r", "h", "i", "j", "k", "l"].includes(
                letter
            )
        )
            days.push("Tuesday");
        if (["a", "b", "c", "d", "e", "t"].includes(letter))
            days.push("Wednesday");
        if (
            [
                "m",
                "i",
                "j",
                "k",
                "l",
                "s",
                "u",
                "v",
                "w",
                "x",
                "y",
                "z",
            ].includes(letter)
        )
            days.push("Thursday");
        if (["a", "f", "g", "h", "n", "o", "p", "q", "r"].includes(letter))
            days.push("Friday");
        if (["m", "s", "t", "u", "v", "w", "x", "y", "z"].includes(letter))
            days.push("Saturday");

        if (days.length === 0) {
            return "Please check the schedule or come Sunday";
        } else {
            return days.join(", ");
        }
    }

    const shareText = `My passport is ready! You can now receive your passport from the Main Department for Immigration & Nationality Affairs. Request Number: ${
        passport.requestNumber
    }, First Name: ${
        passport.firstName
    }. You can collect it starting on ${formatDate(passport.dateOfPublish)}.`;

    const shareUrl = `https://${config.APP_URL}/passport/${passport.requestNumber}`; // Your site's URL for the passport detail
    return (
        <AuthGuestLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight py-4">
                    Passport Details
                </h2>
            }
        >
            <Head title="Details" />
            {/* <main className="mt-10 max-w-[990px] m-auto  mb-20 bg-white/80 dark:bg-black/80  rounded-2xl border border-transparent  hover:border-blue-500 transition-colors duration-300 group mt-8 py-8 selection:bg-[#FF2D20] selection:text-white relative "> */}
            <main className="relative mt-10 max-w-[990px] m-auto mb-20 bg-white/80 dark:bg-black/80 rounded-2xl border border-transparent hover:border-blue-500 transition-colors duration-300 group py-8 selection:bg-[#FF2D20] selection:text-white ">
                {/* Social Media Share Icons */}
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
                        <FaTwitter className="text-blue-400 w-5 h-5 hover:text-blue-600" />
                    </a>

                    {/* Instagram (Instagram doesn't allow direct post via URL scheme, so guide users) */}
                    <a
                        href={`https://www.instagram.com/`}
                        target="_blank"
                        rel="noopener noreferrer"
                        title="Share this on Instagram!"
                    >
                        <FaInstagram className="text-pink-600 w-5 h-5 hover:text-pink-800" />
                    </a>
                </div>

                <div className=" space-y-4 pb-2  flex flex-col space-x-2 justify-between py-6 ">
                    {/* <div className="flex items-center lg:w-[200px] ">
                                <img src={`https://picsum.photos/seed/${Math.random(1,100)}/200`} width="50" className="rounded-xl w-10 h-10 object-cover " />
                            </div> */}
                    <div className="space-y-2 font-bold text-2xl hover:text-red-800 transition-colors duration-500 uppercase flex flex-col items-center">
                        <p>
                            <span className="font-bold text-2xl hover:text-red-800 transition-colors duration-500 uppercase  text-center w-full block">
                                {" "}
                                Federal Democratic Republic of Ethiopia
                            </span>
                        </p>
                        <p className="text-xl text-center ">Passport</p>
                    </div>
                    <div className="space-y-2  text-sm pb-8 pt-8 m-4 pl-4 capitalize ">
                        <p>
                            First Name : <strong>{passport.firstName}</strong>
                        </p>
                        <p>
                            Middle Name : <strong>{passport.middleName}</strong>
                        </p>
                        <p>
                            Last Name : <strong>{passport.lastName}</strong>
                        </p>
                        <p>
                            Request Number :{" "}
                            <strong>{passport.requestNumber}</strong>
                        </p>
                        <p>
                            You Can Receive After :{" "}
                            <strong className="">
                                {formatDate(passport.dateOfPublish)} G.C.
                            </strong>
                        </p>
                        <p>
                            Location : <strong>{passport.location}</strong>
                        </p>
                        <p>
                            Day Of the Week :{" "}
                            <strong>{getDayOfWeek(passport.firstName)}</strong>
                        </p>
                        <p>
                            Exact Time : <strong>3:00 - 9:00 LT</strong>
                        </p>
                    </div>
                    <div className=" space-y-2 font-light text-sm pb-1 mt-4  pt-4 flex flex-col items-center">
                        <p className="text-xs capitalize font-semibold ">
                            Issuing Authority :{" "}
                            <strong className="text-red-800 hover:text-red-800 transition-colors font-semibold duration-500 uppercase ">
                                {" "}
                                MAIN DEPARTMENT FOR IMMIGRATION & NATIONALITY
                                AFFAIRS
                            </strong>
                        </p>
                        <p>published on {formatDate(passport.created_at)}</p>
                    </div>
                </div>
                {/* <div className="absolute top-4 right-4 flex space-x-2">
                    <a href="#" className="text-blue-500 hover:text-blue-600">
                        <FaTelegram size={24} />
                    </a>
                    <a href="#" className="text-blue-600 hover:text-blue-700">
                        <FaFacebookF size={24} />
                    </a>
                    <a href="#" className="text-black hover:text-gray-700">
                        <FaTwitter size={24} />
                    </a>
                    <a href="#" className="text-pink-600 hover:text-pink-700">
                        <FaInstagram size={24} />
                    </a>
                </div> */}
            </main>
        </AuthGuestLayout>
    );
}

export default ShowDetail;
