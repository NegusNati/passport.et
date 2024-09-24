function Footer() {
    return (
        <footer className="bg-gradient-to-r from-slate-100 to-slate-300 dark:from-slate-700 dark:to-zinc-900 dark:text-white/90 pb-10 sm:pt-6 lg:px-8 mt-10">
            <div className="mx-auto max-w-screen-xl px-4 pb-4 pt-16 sm:px-6 lg:px-8 lg:pt-24">
                <div className="text-center">
                    {/* <h2 className="text-3xl font-extrabold text-gray-900 sm:text-5xl">
                                Customise Your Product
                            </h2>

                            <p className="mx-auto mt-4 max-w-sm text-gray-500">
                                Lorem ipsum dolor, sit amet consectetur
                                adipisicing elit. Cum maiores ipsum eos
                                temporibus ea nihil.
                            </p>

                            <a
                                href="#"
                                className="mt-8 inline-block rounded-full border border-indigo-600 px-12 py-3 text-sm font-medium text-indigo-600 hover:bg-indigo-600 hover:text-white focus:outline-none focus:ring active:bg-indigo-500"
                            >
                                Get Started
                            </a> */}
                </div>

                <div className="mt-16 border-t border-gray-100 pt-8 sm:flex sm:items-center sm:justify-between lg:mt-24">
                    <ul className="flex flex-wrap justify-center gap-4 text-xs lg:justify-end">
                        <li>
                            <a
                                 href={route('privacy') + '#terms'}
                                className="text-gray-500 transition hover:opacity-75"
                            >
                                {" "}
                                Terms & Conditions{" "}
                            </a>
                        </li>

                        <li>
                            <a
                                href={route('privacy') + '#privacy'}
                                className="text-gray-500 transition hover:opacity-75"
                            >
                                {" "}
                                Privacy Policy{" "}
                            </a>
                        </li>

                        <li>
                            <a
                               href={route('privacy') + '#cookies'}
                                className="text-gray-500 transition hover:opacity-75"
                            >
                                {" "}
                                Cookies{" "}
                            </a>
                        </li>
                    </ul>
                            <ul>
                            <li>
                                <div className="flex items-center m-auto justify-center ">

                            <a
                                 href="https://t.me/passportdotet_group"
                                className="ttransition   bg-[#FF2D20]/20 dark:bg-[#FF2D20]/80 hover:-translate-y-1 hover:scale-110 hover:bg-red-500 hover:text-white duration-300 p-2 rounded-lg flex items-center m-auto justify-center mx-2"
                            >
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
                                {"  "}
                                <span className="text-white font-bold mx-2">
                                Get Support{" "}
                                </span>
                            </a>
                            </div>
                        </li>
                            </ul>
                    <ul className="mt-8 flex justify-center gap-6 sm:mt-0 lg:justify-end ">
                        <li className="hover:-translate-y-1 hover:scale-110  hover:text-white duration-300">
                            <a
                                href="https://x.com/negusnati"
                                rel="noreferrer"
                                target="_blank"
                                className="text-gray-700 transition  hover:text-white hover:bg-black"
                            >
                                <span className="sr-only">Twitter</span>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    x="0px"
                                    y="0px"
                                    width="25"
                                    height="25"
                                    viewBox="0 0 50 50"
                                >
                                    <path d="M 11 4 C 7.1456661 4 4 7.1456661 4 11 L 4 39 C 4 42.854334 7.1456661 46 11 46 L 39 46 C 42.854334 46 46 42.854334 46 39 L 46 11 C 46 7.1456661 42.854334 4 39 4 L 11 4 z M 11 6 L 39 6 C 41.773666 6 44 8.2263339 44 11 L 44 39 C 44 41.773666 41.773666 44 39 44 L 11 44 C 8.2263339 44 6 41.773666 6 39 L 6 11 C 6 8.2263339 8.2263339 6 11 6 z M 13.085938 13 L 22.308594 26.103516 L 13 37 L 15.5 37 L 23.4375 27.707031 L 29.976562 37 L 37.914062 37 L 27.789062 22.613281 L 36 13 L 33.5 13 L 26.660156 21.009766 L 21.023438 13 L 13.085938 13 z M 16.914062 15 L 19.978516 15 L 34.085938 35 L 31.021484 35 L 16.914062 15 z"></path>
                                </svg>
                            </a>
                        </li>

                         <li className="hover:-translate-y-1 hover:scale-110  hover:text-white duration-300">
                            <a
                                href="https://github.com/negusnati"
                                rel="noreferrer"
                                target="_blank"
                                className="text-gray-700 transition hover:text-white hover:bg-black"
                            >
                                <span className="sr-only">GitHub</span>

                                <svg
                                    className="h-6 w-6"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            </a>
                        </li>

                         <li className="hover:-translate-y-1 hover:scale-110  hover:text-white duration-300">
                            <a
                                href="https://t.me/negusnati"
                                rel="noreferrer"
                                target="_blank"
                                className="text-gray-700 transition hover:text-white hover:bg-black"
                            >
                                <span className="sr-only">Telegram</span>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    x="0px"
                                    y="0px"
                                    width="25"
                                    height="25"
                                    viewBox="0 0 50 50"
                                >
                                    <path d="M25,2c12.703,0,23,10.297,23,23S37.703,48,25,48S2,37.703,2,25S12.297,2,25,2z M32.934,34.375	c0.423-1.298,2.405-14.234,2.65-16.783c0.074-0.772-0.17-1.285-0.648-1.514c-0.578-0.278-1.434-0.139-2.427,0.219	c-1.362,0.491-18.774,7.884-19.78,8.312c-0.954,0.405-1.856,0.847-1.856,1.487c0,0.45,0.267,0.703,1.003,0.966	c0.766,0.273,2.695,0.858,3.834,1.172c1.097,0.303,2.346,0.04,3.046-0.395c0.742-0.461,9.305-6.191,9.92-6.693	c0.614-0.502,1.104,0.141,0.602,0.644c-0.502,0.502-6.38,6.207-7.155,6.997c-0.941,0.959-0.273,1.953,0.358,2.351	c0.721,0.454,5.906,3.932,6.687,4.49c0.781,0.558,1.573,0.811,2.298,0.811C32.191,36.439,32.573,35.484,32.934,34.375z"></path>
                                </svg>
                            </a>
                        </li>
                         <li className="hover:-translate-y-1 hover:scale-110  hover:text-white duration-300">
                            <a
                                href="https://www.instagram.com/negusnati/"
                                rel="noreferrer"
                                target="_blank"
                                className="text-gray-700 transition hover:text-white hover:bg-black"
                            >
                                <span className="sr-only">Instagram</span>

                                <svg
                                    className="h-6 w-6"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        fillRule="evenodd"
                                        d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"
                                        clipRule="evenodd"
                                    />
                                </svg>
                            </a>
                        </li>
                         <li className="hover:-translate-y-1 hover:scale-110  hover:text-white duration-300">
                            <a
                                href="https://www.linkedin.com/in/negusnati/"
                                rel="noreferrer"
                                target="_blank"
                                className="text-gray-700 transition hover:text-white hover:bg-black"
                            >
                                <span className="sr-only">LinkedIn</span>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    x="0px"
                                    y="0px"
                                    width="25"
                                    height="25"
                                    viewBox="0 0 50 50"
                                >
                                    <path d="M 9 4 C 6.2504839 4 4 6.2504839 4 9 L 4 41 C 4 43.749516 6.2504839 46 9 46 L 41 46 C 43.749516 46 46 43.749516 46 41 L 46 9 C 46 6.2504839 43.749516 4 41 4 L 9 4 z M 9 6 L 41 6 C 42.668484 6 44 7.3315161 44 9 L 44 41 C 44 42.668484 42.668484 44 41 44 L 9 44 C 7.3315161 44 6 42.668484 6 41 L 6 9 C 6 7.3315161 7.3315161 6 9 6 z M 14 11.011719 C 12.904779 11.011719 11.919219 11.339079 11.189453 11.953125 C 10.459687 12.567171 10.011719 13.484511 10.011719 14.466797 C 10.011719 16.333977 11.631285 17.789609 13.691406 17.933594 A 0.98809878 0.98809878 0 0 0 13.695312 17.935547 A 0.98809878 0.98809878 0 0 0 14 17.988281 C 16.27301 17.988281 17.988281 16.396083 17.988281 14.466797 A 0.98809878 0.98809878 0 0 0 17.986328 14.414062 C 17.884577 12.513831 16.190443 11.011719 14 11.011719 z M 14 12.988281 C 15.392231 12.988281 15.94197 13.610038 16.001953 14.492188 C 15.989803 15.348434 15.460091 16.011719 14 16.011719 C 12.614594 16.011719 11.988281 15.302225 11.988281 14.466797 C 11.988281 14.049083 12.140703 13.734298 12.460938 13.464844 C 12.78117 13.19539 13.295221 12.988281 14 12.988281 z M 11 19 A 1.0001 1.0001 0 0 0 10 20 L 10 39 A 1.0001 1.0001 0 0 0 11 40 L 17 40 A 1.0001 1.0001 0 0 0 18 39 L 18 33.134766 L 18 20 A 1.0001 1.0001 0 0 0 17 19 L 11 19 z M 20 19 A 1.0001 1.0001 0 0 0 19 20 L 19 39 A 1.0001 1.0001 0 0 0 20 40 L 26 40 A 1.0001 1.0001 0 0 0 27 39 L 27 29 C 27 28.170333 27.226394 27.345035 27.625 26.804688 C 28.023606 26.264339 28.526466 25.940057 29.482422 25.957031 C 30.468166 25.973981 30.989999 26.311669 31.384766 26.841797 C 31.779532 27.371924 32 28.166667 32 29 L 32 39 A 1.0001 1.0001 0 0 0 33 40 L 39 40 A 1.0001 1.0001 0 0 0 40 39 L 40 28.261719 C 40 25.300181 39.122788 22.95433 37.619141 21.367188 C 36.115493 19.780044 34.024172 19 31.8125 19 C 29.710483 19 28.110853 19.704889 27 20.423828 L 27 20 A 1.0001 1.0001 0 0 0 26 19 L 20 19 z M 12 21 L 16 21 L 16 33.134766 L 16 38 L 12 38 L 12 21 z M 21 21 L 25 21 L 25 22.560547 A 1.0001 1.0001 0 0 0 26.798828 23.162109 C 26.798828 23.162109 28.369194 21 31.8125 21 C 33.565828 21 35.069366 21.582581 36.167969 22.742188 C 37.266572 23.901794 38 25.688257 38 28.261719 L 38 38 L 34 38 L 34 29 C 34 27.833333 33.720468 26.627107 32.990234 25.646484 C 32.260001 24.665862 31.031834 23.983076 29.517578 23.957031 C 27.995534 23.930001 26.747519 24.626988 26.015625 25.619141 C 25.283731 26.611293 25 27.829667 25 29 L 25 38 L 21 38 L 21 21 z"></path>
                                </svg>
                            </a>
                        </li>
                    </ul>
                </div>
                <div className="mt-8 text-sm text-gray-500 dark:text-gray-400 text-center">
                    &copy;
                    {new Date().getFullYear()} Passport.ET. All rights reserved.
                </div>
            </div>
        </footer>
    );
}

export default Footer;
