import ApplicationLogo from "@/Components/ApplicationLogo";
import Footer from "@/Components/Footer";
import PricingSection from "@/Components/PricingSection";
import { Link, Head } from "@inertiajs/react";

export default function Welcome({ auth, laravelVersion, phpVersion }) {
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
    // const { props } = usePage();
    // const form = useForm({
    //     // Add any form fields you need here
    // });

    // const submit = () => {
    //     form.post(route("pay"));
    // };

    return (
        <>
            <Head title="Welcome" />
            <div className="bg-gradient-to-r from-slate-100 to-slate-300 dark:from-slate-700 dark:to-zinc-900 dark:text-white/90 pb-10  rounded-xl pb-10">
                <img
                    id="background"
                    className="absolute -left-20 top-0 max-w-[1100px]"
                    src="https://laravel.com/assets/img/welcome/background.svg"
                />
                <div className="relative min-h-screen pt-4 px-1 pt-50 selection:bg-[#FF2D20] selection:text-white sm:px-4 sm:px-6 lg:px-8">
                    {/* <div className="relative w-full max-w-2xl px-6 lg:max-w-7xl"> */}
                    <header className="flex justify-between space-x-8 items-center gap-2  lg:grid-cols-3 ">
                        <div className="mr-auto pt-2">
                            {/* <svg
                                width="150"
                                height="150"
                                viewBox="0 0 100 70"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <rect
                                    x="0"
                                    y="0"
                                    width="100"
                                    height="70"
                                    fill="#c71508"
                                    rx="5"
                                />
                                <circle cx="50" cy="35" r="10" fill="#F8E231" />
                                <path
                                    d="M45 30 L55 30 L55 40 L45 40 Z"
                                    fill="#F8E231"
                                />
                                <path
                                    d="M50 27.5 L51.76 32.45 L56.97 32.45 L52.60 35.55 L54.36 40.5 L50 37.5 L45.64 40.5 L47.40 35.55 L43.03 32.45 L48.24 32.45 Z"
                                    fill="none"
                                    stroke="black"
                                    strokeWidth="1"
                                />
                                <text
                                    x="50"
                                    y="15"
                                    fontSize="6"
                                    fontFamily="Arial"
                                    fill="#FFFFFF"
                                    textAnchor="middle"
                                >
                                    REPUBLIC OF ETHIOPIA
                                </text>
                                <text
                                    x="50"
                                    y="60"
                                    fontSize="6"
                                    fontFamily="Arial"
                                    fill="#FFFFFF"
                                    textAnchor="middle"
                                >
                                    PASSPORT
                                </text>
                                <line
                                    x1="10"
                                    y1="19"
                                    x2="90"
                                    y2="19"
                                    stroke="#FFFFFF"
                                    strokeWidth="2"
                                />
                                <line
                                    x1="10"
                                    y1="50"
                                    x2="90"
                                    y2="50"
                                    stroke="#FFFFFF"
                                    strokeWidth="2"
                                />
                            </svg> */}

                            <ApplicationLogo />
                        </div>
                        <nav className="ml-auto flex justify-between space-x-4 ">
                            {auth.user ? (
                                <Link
                                    href={route("dashboard")}
                                    className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route("login")}
                                        className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        href={route("register")}
                                        className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                                    >
                                        Register
                                    </Link>
                                </>
                            )}
                        </nav>
                    </header>
                    <main className="bg-gray-50 bg-transparent w-full sm:py-20 lg:px-8 ">
                        <div className="mx-auto max-w-screen-xl px-4 py-32 lg:flex  lg:items-center mb-20 ">
                            <div className="mx-auto max-w-xl text-center">
                                <h1 className="text-3xl font-extrabold sm:text-5xl capitalize">
                                    Is your Passport Ready Yet?
                                    <strong className="font-extrabold text-red-700 sm:block">
                                        {" "}
                                        Find Out Now!{" "}
                                    </strong>
                                </h1>

                                <p className="mt-4 sm:text-xl/relaxed">
                                    You can look at the <span  className="text-red-700 font-semibold">latest</span>{" "}
                                    passports published by the government of
                                    Ethiopia.
                                </p>

                                <div className="mt-8 flex flex-wrap justify-center gap-4">
                                    <a
                                        className="block w-full rounded px-12 py-3 text-sm font-medium text-white shadow  focus:outline-none focus:ring active:bg-red-500 sm:w-auto transition ease-in-out delay-100 bg-blue-500 hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500 duration-300"
                                        href="#pricing"
                                    >
                                        Find Passport
                                    </a>

                                    <a
                                        className="block w-full rounded px-12 py-3 text-sm font-medium text-red-600 bg-gray-100 shadow  focus:outline-none focus:ring active:text-red-500 sm:w-auto  transition ease-in-out delay-100  hover:-translate-y-1 hover:scale-110 hover:bg-red-700 hover:text-white duration-300"
                                        href={route("login")}
                                    >
                                        {auth ? "Dashboard" : "Log In"}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </main>

                    <PricingSection id="pricing" />
                    <Footer />
                </div>
            </div>
        </>
    );
}
