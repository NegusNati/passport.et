function PricingSection_Depriciated() {
    return (
        <section className="dark:bg-gradient-to-r py-12 m-auto bg-gradient-to-b from-slate-600 to-slate-300 dark:from-slate-600 dark:black/80 dark:text-white/90 rounded-xl pb-10">
            <div className="mx-auto max-w-3xl px-4 pt-8 mb-8 sm:px-6 sm:py-20 lg:px-8">
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:items-center md:gap-8 ">
                    <div className="rounded-2xl border border-indigo-600 p-6 shadow-sm ring-1 ring-indigo-600 sm:order-last sm:px-8 lg:p-12 bg-white/80 dark:bg-black/80 text-black/80 dark:text-white/80">
                        <div className="text-center">
                            <h2 className="text-lg font-medium">
                                Premium
                                <span className="sr-only">Plan</span>
                            </h2>

                            <p className="mt-2 sm:mt-4">
                                <strong className="text-3xl font-bold sm:text-4xl">
                                    {" "}
                                    20 Birr{" "}
                                </strong>

                                <span className="text-sm font-medium  capitalize">
                                    for a Full month
                                </span>
                            </p>
                        </div>

                        <ul className="mt-6 space-y-2">
                            <li className="flex items-center gap-1">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    className="size-5 text-indigo-700"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M4.5 12.75l6 6 9-13.5"
                                    />
                                </svg>

                                <span className=" capitalize">
                                    {" "}
                                    Latest passport information{" "}
                                </span>
                            </li>
                            <li className="flex items-center gap-1">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    className="size-5 text-indigo-700"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M4.5 12.75l6 6 9-13.5"
                                    />
                                </svg>

                                <span className=" capitalize">
                                    {" "}
                                    1000 queries per hour{" "}
                                </span>
                            </li>
                            <li className="flex items-center gap-1">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    className="size-5 text-indigo-700"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M4.5 12.75l6 6 9-13.5"
                                    />
                                </svg>

                                <span className=" capitalize">
                                    {" "}
                                    Date of receival{" "}
                                </span>
                            </li>

                            <li className="flex items-center gap-1">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    className="size-5 text-indigo-700"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M4.5 12.75l6 6 9-13.5"
                                    />
                                </svg>

                                <span className=" capitalize">
                                    {" "}
                                    Detailed information{" "}
                                </span>
                            </li>

                            <li className="flex items-center gap-1">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    className="size-5 text-indigo-700"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M4.5 12.75l6 6 9-13.5"
                                    />
                                </svg>

                                <span className=" capitalize">
                                    {" "}
                                    community support{" "}
                                </span>
                            </li>

                            <li className="flex items-center gap-1">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    className="size-5 text-indigo-700"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M4.5 12.75l6 6 9-13.5"
                                    />
                                </svg>

                                <span className=""> 24/7 support </span>
                            </li>
                        </ul>

                        <a
                            id="payment"
                            // href="/payment"
                            href={route("register", { amount: 20 })}
                            // href={route("payment", { amount: 20 })}
                            className="mt-8 block rounded-full border border-indigo-600  px-12 py-3 text-center text-sm font-medium text-white  hover:ring-1 hover:ring-indigo-700 focus:outline-none focus:ring active:text-indigo-500  transition ease-in-out delay-100 bg-blue-500 hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500 duration-300"
                        >
                            Get Started
                        </a>
                    </div>

                    <div className="rounded-2xl border border-blue-200 p-6 shadow-sm sm:px-8 lg:p-12 bg-white/80 dark:bg-black/80 text-black/80 dark:text-white/80">
                        <div className="text-center">
                            <h2 className="text-lg font-medium ">
                                Standard
                                <span className="sr-only">Plan</span>
                            </h2>

                            <p className="mt-2 sm:mt-4">
                                <strong className="text-3xl font-bold sm:text-4xl">
                                    {" "}
                                    Free
                                </strong>

                                <span className="text-sm font-medium">
                                    {" "}
                                    For limited time
                                </span>
                            </p>
                        </div>

                        <ul className="mt-6 space-y-2">
                            <li className="flex items-center gap-1">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    className="size-5 text-indigo-700"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M4.5 12.75l6 6 9-13.5"
                                    />
                                </svg>

                                <span className=" capitalize">
                                    {" "}
                                    passport information{" "}
                                </span>
                            </li>
                            <li className="flex items-center gap-1">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    className="size-5 text-indigo-700"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M4.5 12.75l6 6 9-13.5"
                                    />
                                </svg>

                                <span className=" capitalize">
                                    {" "}
                                    120 queries per hour{" "}
                                </span>
                            </li>

                            <li className="flex items-center gap-1">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    className="size-5 text-indigo-700"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M4.5 12.75l6 6 9-13.5"
                                    />
                                </svg>

                                <span className=" capitalize">
                                    {" "}
                                    Date of receival{" "}
                                </span>
                            </li>

                            <li className="flex items-center gap-1">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    className="size-5 text-indigo-700"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M4.5 12.75l6 6 9-13.5"
                                    />
                                </svg>

                                <span className=" capitalize">
                                    {" "}
                                    Detailed information{" "}
                                </span>
                            </li>

                            <li className="flex items-center gap-1">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth="1.5"
                                    stroke="currentColor"
                                    className="size-5 text-indigo-700"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        d="M4.5 12.75l6 6 9-13.5"
                                    />
                                </svg>

                                <span className=""> Help center access </span>
                            </li>
                        </ul>

                        <a
                            // href={route("payment", { amount: 10 })}
                            href={route("register")}
                            className="mt-8 block rounded-full border border-indigo-600 px-12 py-3 text-center text-sm font-medium text-indigo-600 hover:ring-1 hover:ring-indigo-600 focus:outline-none focus:ring active:text-indigo-500  transition ease-in-out delay-100 bg-blue-500 hover:-translate-y-1 hover:scale-110  duration-300"
                        >
                            Get Started
                        </a>
                    </div>
                </div>
            </div>
        </section>
    );
}

export default PricingSection_Depriciated;
