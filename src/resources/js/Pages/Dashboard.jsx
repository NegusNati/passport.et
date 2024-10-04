import DismissibleBanner from "@/Components/DismissibleBanner";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";
import { Head, Link } from "@inertiajs/react";
import { React, useState } from "react";

export default function Dashboard({ auth }) {
    const [idValue, setIdValue] = useState("");
    const handleImageError = () => {
        document
            .getElementById("screenshot-container")
            ?.classList.add("!hidden");
        document.getElementById("docs-card")?.classList.add("!row-span-1");
        document
            .getElementById("docs-card-content")
            ?.classList.add("!flex-row");
        document.getElementById("background")?.classList.add("!hidden");
    };
    // console.log(idValue);
    return (
        <AuthGuestLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-lg sm:text-xl  text-gray-800 dark:text-gray-200 leading-tight">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />
            <DismissibleBanner
                text="Welcome to Passport.ET, your one stop solution for checking your passport status"
                bgColor={"bg-red-400"}
            />

            <div className="pt-12 py-12 pb-20 mb-40 bg-gradient-to-r from-slate-100 to-slate-300 dark:from-slate-700 dark:to-zinc-900 dark:text-white/90">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 ">
                    <div className="bg-gradient-to-r from-slate-500 to-slate-100 dark:from-slate-700 dark:to-zinc-900 dark:text-white/50 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100 space-y-6">
                            <div className="">
                                <Link
                                    href={route("passport")}
                                    className="flex items-start justify-between gap-2 sm:gap-4 rounded-lg bg-white p-4 sm:p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]"
                                >
                                    <div className="flex items-start gap-2 sm:gap-4">
                                        <div className="flex size-10 sm:size-12 md:size-16 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10">
                                            <svg
                                                className="size-5 sm:size-6"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <g fill="#FF2D20">
                                                    <path d="M11 2a9 9 0 1 0 5.66 16.16l4.12 4.12a1 1 0 0 0 1.41-1.41l-4.12-4.12A9 9 0 0 0 11 2zm0 2a7 7 0 1 1 0 14 7 7 0 0 1 0-14z" />
                                                </g>
                                            </svg>
                                        </div>

                                        <div className="">
                                            <h2 className="text-lg sm:text-xl  font-semibold text-black dark:text-white">
                                                Search Passport
                                            </h2>

                                            <p className="mt-2 sm:mt-4 text-xs sm:text-sm/relaxed ">
                                                Do you want to see if your
                                                Passport is ready for pick up?
                                                shearch for it by NAME or
                                                Request ID.
                                            </p>
                                        </div>
                                    </div>
                                    <svg
                                        className="size-6 shrink-0 self-center stroke-[#FF2D20]"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        strokeWidth="1.5"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"
                                        />
                                    </svg>
                                </Link>
                            </div>
                            <div className="">
                                <Link
                                    href={route("passport.all")}
                                    className="flex items-start justify-between gap-2 sm:gap-4 rounded-lg bg-white p-4 sm:p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]"
                                >
                                    <div className="flex items-start gap-2 sm:gap-4">
                                        <div className="flex size-10 sm:size-12 md:size-16 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 ">
                                            <svg
                                                className="size-5 sm:size-6"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <g fill="#FF2D20">
                                                    <path d="M3 6h18c.55 0 1-.45 1-1s-.45-1-1-1H3c-.55 0-1 .45-1 1s.45 1 1 1zm0 5h18c.55 0 1-.45 1-1s-.45-1-1-1H3c-.55 0-1 .45-1 1s.45 1 1 1zm0 5h18c.55 0 1-.45 1-1s-.45-1-1-1H3c-.55 0-1 .45-1 1s.45 1 1 1zm0 5h18c.55 0 1-.45 1-1s-.45-1-1-1H3c-.55 0-1 .45-1 1s.45 1 1 1z" />
                                                </g>
                                            </svg>
                                        </div>

                                        <div className="">
                                            <h2 className="text-lg sm:text-xl  font-semibold text-black dark:text-white capitalize">
                                                Today's Releases
                                            </h2>
                                            <p className="mt-2 sm:mt-4 text-xs sm:text-sm/relaxed ">
                                                The latest daily updated
                                                passports, look through all
                                                passports going back from 5+
                                                months back. you can take
                                                advantage this information to
                                                get your passport.
                                            </p>
                                        </div>
                                    </div>

                                    <svg
                                        className="size-6 shrink-0 self-center stroke-[#FF2D20]"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        strokeWidth="1.5"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"
                                        />
                                    </svg>
                                </Link>
                            </div>

                            <div className="">
                                <Link
                                    href={route("passport.locations")}
                                    className="flex items-start justify-between gap-2 sm:gap-4 rounded-lg bg-white p-4 sm:p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]"
                                >
                                    <div className="flex items-start gap-2 sm:gap-4">
                                        <div className="flex size-10 sm:size-12 md:size-16 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 ">
                                            <svg
                                                className="size-5 sm:size-6"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 384 512"
                                                fill="#FF2D20"
                                            >
                                                <path d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z" />
                                            </svg>
                                        </div>

                                        <div className="">
                                            <h2 className="text-lg sm:text-xl  font-semibold text-black dark:text-white capitalize">
                                                By City
                                            </h2>
                                            <p className="mt-2 sm:mt-4 text-xs sm:text-sm/relaxed ">
                                                From Cities Like : Addis Ababa,
                                                Dire Dawa, Adama, Hawassa,
                                                Jimma, Bahir Dar, Jijiga,
                                                Mekele, Dessie, Asosa, Semera,
                                                Hosaena, Gambela
                                            </p>
                                        </div>
                                    </div>

                                    <svg
                                        className="size-6 shrink-0 self-center stroke-[#FF2D20]"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        strokeWidth="1.5"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"
                                        />
                                    </svg>
                                </Link>
                            </div>

                            <div className="">
                                <Link
                                    href={route("telegram.index")}
                                    className="flex items-start justify-between gap-2 sm:gap-4 rounded-lg bg-white p-4 sm:p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]"
                                >
                                    <div className="flex items-start gap-2 sm:gap-4">
                                        <div className="flex size-10 sm:size-12 md:size-16 shrink-0 items-center justify-center rounded-full bg-[#FF2D20]/10 ">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 240 240"
                                                className="size-7 shrink-0 self-center stroke-[#FF2D20]"
                                            >
                                                <defs>
                                                    <linearGradient
                                                        id="telegramGradient"
                                                        x1="0.6667"
                                                        y1="0.1667"
                                                        x2="0.4167"
                                                        y2="0.75"
                                                    >
                                                        <stop
                                                            stopColor="#37aee2"
                                                            offset="0"
                                                        />
                                                        <stop
                                                            stopColor="#1e96c8"
                                                            offset="1"
                                                        />
                                                    </linearGradient>
                                                </defs>
                                                <circle
                                                    cx="120"
                                                    cy="120"
                                                    r="120"
                                                    fill="url(#telegramGradient)"
                                                />
                                                <path
                                                    fill="#c8daea"
                                                    d="M98 175c-3.9 0-3.2-1.5-4.6-5.2L82 132.2l88-52.2"
                                                />
                                                <path
                                                    fill="#a9c9dd"
                                                    d="M98 175c3 0 4.3-1.4 6-3l16-15.6-20-12"
                                                />
                                                <path
                                                    fill="#fff"
                                                    d="M100 144.4l48.4 35.7c5.5 3 9.5 1.5 10.9-5.1l19.7-92.8c2-8-3.1-11.7-8.4-9.3L55 117.5c-7.9 3.2-7.8 7.6-1.4 9.5l29.7 9.3 68.7-43.3c3.2-2 6.2-.9 3.8 1.3"
                                                />
                                            </svg>
                                        </div>

                                        <div className="">
                                            <h2 className="text-lg sm:text-xl  font-semibold text-black dark:text-white capitalize">
                                                Telegram Notification
                                            </h2>

                                            <p className="mt-2 sm:mt-4 text-xs sm:text-sm/relaxed ">
                                                Register for automatic
                                                notification in Telegram for
                                                when your passport is ready for
                                                pick up.
                                            </p>
                                        </div>
                                    </div>
                                    <svg
                                        className="size-6 shrink-0 self-center stroke-[#FF2D20]"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        strokeWidth="1.5"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"
                                        />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthGuestLayout>
    );
}
