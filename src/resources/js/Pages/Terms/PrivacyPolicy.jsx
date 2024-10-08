import Footer from "@/Components/Footer";
import ApplicationLogo from "@/Components/ApplicationLogo";
import { Link, Head } from "@inertiajs/react";

function PrivacyPolicy({ auth }) {
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
    return (
        <>
            <Head title="Welcome" />
            <div className="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 pb-10">
                <img
                    id="background"
                    className="absolute -left-20 top-0 max-w-[1100px]"
                    src="https://laravel.com/assets/img/welcome/background.svg"
                    alt="backgorund"
                />
                <div className="relative min-h-screen pt-4 pt-50 selection:bg-[#FF2D20] selection:text-white px-4 sm:px-6 lg:px-8">
                    {/* <div className="relative w-full max-w-2xl px-6 lg:max-w-7xl"> */}
                    <header className="flex justify-between space-x-8 items-center gap-2  lg:grid-cols-3 ">
                        <div className="mr-auto pt-2">

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
                        <div className="mx-auto max-w-screen-xl px-4  lg:items-center mb-20 space-y-8">
                            <h1 className=" text-3xl font-bold text-center text-gray-900 dark:text-white pb-8 ">
                                Privacy
                            </h1>
                            <h2
                                id="privacy"
                                className="text-2xl font-semibold  text-gray-900 dark:text-white  "
                            >
                                Our Privacy Policy
                            </h2>
                            <p>
                                This Privacy Policy includes important
                                information about your personal data and we
                                encourage you to read it carefully.
                            </p>
                            <p>
                                It describes your privacy rights regarding how
                                and when we collect, use, store, share, and
                                protect your information across our website,
                                APIs, applications, email notifications, and
                                tools regardless of how you use or access them.
                            </p>
                            <h2 className=" font-semibold  text-gray-900 dark:text-white ">
                                Overview
                            </h2>
                            <p>
                                This Privacy Policy describes how we collect and
                                use your personal information. We collect and
                                use personal information solely for fulfilling
                                the purposes specified by us and for other
                                additional purposes. This refers to the personal
                                information you submit when you register (e.g.,
                                email address, company name, password, bank
                                account number, etc.) it may also include
                                anonymous information that is linked to you, for
                                example, your IP Address. It should be relevant
                                to the purposes for which it is to be used, and,
                                to the extent necessary for those purposes,
                                should be accurate, complete, and up-to-date.
                                The following lists what we use your personal
                                information for.These may be updated from time
                                to time.
                            </p>
                            <ul className="list-disc list-inside space-y-2 ">
                                <li>
                                    To provide you with access to our service.
                                </li>
                                <li>To verify the information provided.</li>
                                <li>
                                    To prevent, detect and manage any illegal
                                    and fraudulent activity.
                                </li>
                                <li>To improve our services to you.</li>

                                <li>
                                    To address any inappropriate use of our
                                    services.
                                </li>
                                <li>
                                    To update our website layout, database, and
                                    content.
                                </li>
                                <li>
                                    To target advertisements, newsletters, and
                                    service updates.
                                </li>
                            </ul>
                            <p>
                                Resolve disputes that may occur. Additional
                                personal information may be gotten from
                                third-party applications and other
                                identification/verification services. For
                                example, from your financial institution. Once
                                you begin to use our services, we keep all
                                records of your transactions and we will not
                                share or disclose your personal information with
                                a third party without your consent. Web Visitors
                                We do not collect information from web visitors.
                                We may however collect
                                non-personally-identifiable information to
                                enable us to target advertisements to you and
                                monitor and improve our website and services.
                                This too, will not be shared or disclosed to
                                third parties without your consent. Protecting
                                Your Information We will protect your personal
                                information by using global security safeguards
                                against loss or theft, as well as against any
                                unauthorized access, risk of loss, disclosure,
                                copying, misuse, or modification. Other security
                                measures include but are not limited to,
                                firewall, data encryption, and granting access
                                only to employees in order to fulfill their job
                                responsibilities. We are committed to conducting
                                our business in accordance with these principles
                                in order to ensure that the confidentiality of
                                personal information is protected and
                                maintained.
                            </p>
                            <h2
                                id="cookies"
                                className="text-2xl font-semibold  text-gray-900 dark:text-white "
                            >
                                Cookies
                            </h2>
                            <p>
                                Like many other websites, we use cookies to
                                identify you as a user and to customize and
                                improve our services. A cookie is a small data
                                file that is transferred to your computer or
                                mobile device. It enables us to remember your
                                account log-in information, IP addresses, web
                                traffic, number of times you visit, date, and
                                time of visits. Some browsers may automatically
                                accept cookies while some can be modified to
                                decline cookies or alert you when a website
                                wants to place a cookie on your computer. If you
                                do choose to disable cookies, it may limit your
                                ability to use our website.
                            </p>

                            <h2
                                id="terms"
                                className="text-2xl font-semibold text-gray-900 dark:text-white"
                            >
                                Terms and Conditions
                            </h2>
                            <p>
                                By using our website and services, you agree to
                                the following terms and conditions:
                            </p>
                            <ul className="list-disc list-inside space-y-2">
                                <li>
                                    You must be at least 12 years old to use our
                                    services.
                                </li>
                                <li>
                                    You are responsible for maintaining the
                                    confidentiality of your account and
                                    password.
                                </li>
                                <li>
                                    You agree not to use our services for any
                                    illegal or unauthorized purposes.
                                </li>
                                <li>
                                    We reserve the right to modify or
                                    discontinue our services at any time without
                                    notice.
                                </li>
                                <li>
                                    We are not responsible for any damages or
                                    losses resulting from the use of our
                                    services.
                                </li>
                            </ul>
                            <p>
                                These terms and conditions are subject to change
                                without notice. Your continued use of our
                                website and services constitutes your acceptance
                                of any updated terms and conditions.
                            </p>
                            <h2 className=" font-semibold  text-gray-900 dark:text-white ">
                                {" "}
                                Data Storage{" "}
                            </h2>
                            <p>
                                We will only retain personal information on our
                                servers for as long as an account is active.
                                Once an account has been deactivated, your
                                information is stored on our servers for as long
                                as necessarily obligated by law.
                            </p>
                            <h2 className=" font-semibold  text-gray-900 dark:text-white ">
                                {" "}
                                Changes to the Privacy Policy
                            </h2>
                            <p>
                                We may update this Privacy Policy from time to
                                time. If we do, we will notify you by email or
                                posting on our website. The updated Privacy
                                Policy will be effective immediately upon
                                posting or emailing it to you. If you continue
                                to use our website after the updated Privacy
                                Policy is posted, you will be bound by the
                                updated Privacy Policy.
                            </p>
                            <h2 className=" font-semibold  text-gray-900 dark:text-white ">
                                Contact Us
                            </h2>
                            <p>
                                If you have any questions about this Privacy
                                Policy, please contact us at
                                <a
                                    href="mailto:negusnati@gmail.com"
                                    className="text-blue-500"
                                >
                                    {" "}
                                    negusnati@gmail.com
                                </a>
                            </p>
                            <p>
                                This Privacy Policy was last updated in July
                                2024.
                            </p>
                        </div>
                    </main>

                    <Footer />
                </div>
            </div>
        </>
    );
}

export default PrivacyPolicy;
