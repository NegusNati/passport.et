import { Head, Link } from "@inertiajs/react"
import Welcome from './../../../../vendor/laravel/breeze/stubs/inertia-react-ts/resources/js/Pages/Welcome';

function RateLimitExceeded({ message }) {
    return (
        <section className="bg-white dark:bg-gray-900   pt-12 pb-20 sm:pb-10 sm:pt-2 space-y-10">
             <Head title="Too Many Requests" />
            <div className="container min-h-screen px-6 py-12 mx-auto lg:flex lg:items-center lg:gap-12 ">
                <div className="wf-ull lg:w-1/2">
                    {/* <p className="text-sm font-medium text-blue-500 dark:text-blue-400">

                    </p> */}
                    <h1 className="mt-3 text-2xl font-semibold text-blue-500 dark:text-blue-400 md:text-3xl">
                        {message} Please try again later.
                    </h1>
                    <p className="mt-4 text-gray-500 dark:text-gray-400">
                       Error 429 | Too Many Requests
                    </p>

                    <div className="flex items-center mt-6 gap-x-3 pt-8 sm:pt-2  ">
                        <Link
                            href={route("dashboard")}
                            className="flex items-center justify-center w-1/2 px-5 py-2 text-sm text-gray-700 transition-colors duration-200 bg-white border rounded-lg gap-x-2 sm:w-auto dark:hover:bg-gray-800 dark:bg-gray-900 hover:bg-gray-100 dark:text-gray-200 dark:border-gray-700"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                strokeWidth="1.5"
                                stroke="currentColor"
                                className="w-5 h-5 rtl:rotate-180"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    d="M6.75 15.75L3 12m0 0l3.75-3.75M3 12h18"
                                />
                            </svg>

                            <span>Take me home</span>
                        </Link>

                        <Link
                            // href={route("payment")}
                            href={route("welcome")}
                            className="w-1/2 px-5 py-2 text-sm tracking-wide text-white transition-colors duration-200 bg-blue-500 rounded-lg shrink-0 sm:w-auto hover:bg-blue-600 dark:hover:bg-blue-500 dark:bg-blue-600"
                        >
                            Welcome Page
                        </Link>
                    </div>
                </div>

                <div className="relative w-full mt-12 lg:w-1/2 lg:mt-0">
                <img
                className="w-full max-w-lg lg:mx-auto"
                src="/illustration.svg"
                alt="Illustration"
            />
                </div>
            </div>
        </section>
    );
}

export default RateLimitExceeded;
