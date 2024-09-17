import ApplicationLogo from "@/Components/ApplicationLogo";
import Footer from "@/Components/Footer";
import PricingSection from "@/Components/PricingSection";
import { Link, Head } from "@inertiajs/react";
import { motion } from "framer-motion";
import { useState , useEffect, useRef} from "react";

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
            <div className="bg-gradient-to-r from-slate-100 to-slate-300 dark:from-slate-700 dark:to-zinc-900 dark:text-white/90 rounded-xl pb-10">
                <img
                    id="background"
                    className="absolute -left-20 top-0 max-w-[1100px]"
                    src="https://laravel.com/assets/img/welcome/background.svg"
                />
                <div className="relative min-h-screen pt-4 px-1 pt-50 selection:bg-[#FF2D20] selection:text-white sm:px-4 lg:px-8">
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
                    <main className="bg-transparent w-full/80% sm:py-20 lg:px-8">
                        <HeroSection auth={auth} />
                        <div className="my-20"></div>
                        <ServicesSection />
                        <ProcessSection />
                        <PricingSection id="pricing" />
                        <FAQSection />
                        <TestimonialsSection />
                        <Footer />
                    </main>
                </div>
            </div>
        </>
    );
}

function HeroSection({ auth }) {
    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            className="mx-auto max-w-screen-xl px-4 py-32 lg:flex lg:items-center mb-20"
        >
            <div className="mx-auto max-w-xl text-center">
                <h1 className="text-3xl font-extrabold sm:text-5xl capitalize">
                    Is your Passport Ready?
                    <strong className="font-extrabold text-red-700 sm:block">
                        Find Out Now now!
                    </strong>
                </h1>
                <p className="mt-4 sm:text-xl/relaxed">
                    Check the{" "}
                    <span className="text-red-700 font-semibold">latest</span>{" "}
                    passport status published by the Ethiopian Immigration
                    Office.
                </p>
                <div className="mt-8 flex flex-wrap justify-center gap-4">
                    <a
                        className="block w-full rounded px-12 py-3 text-sm font-medium text-white shadow focus:outline-none focus:ring active:bg-red-500 sm:w-auto transition ease-in-out delay-100 bg-blue-500 hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500 duration-300"
                        href="#pricing"
                    >
                        Check Passport Status
                    </a>
                    <a
                        className="block w-full rounded px-12 py-3 text-sm font-medium text-red-600 bg-gray-100 shadow focus:outline-none focus:ring active:text-red-500 sm:w-auto transition ease-in-out delay-100 hover:-translate-y-1 hover:scale-110 hover:bg-red-700 hover:text-white duration-300"
                        href={route("login")}
                    >
                        {auth ? "Dashboard" : "Log In"}
                    </a>
                </div>
            </div>
        </motion.div>
    );
}


function ServicesSection() {
    const scrollRef = useRef(null);
    const services = [
        { title: "Passport Status Check", description: "Quickly check if your passport is ready for collection." },
        { title: "Registration Information", description: "Learn how to register for a new passport or renew an existing one." },
        { title: "Processing Time Updates", description: "Get the latest information on passport processing times." },
        { title: "Document Requirements", description: "Find out what documents you need for your passport application." },
        { title: "Online Application Assistance", description: "Step-by-step guidance for completing your online passport application." },
        { title: "Expedited Service", description: "Information on how to expedite your passport processing for urgent travel needs." },
        { title: "Lost Passport Support", description: "Guidance on what to do if your passport is lost or stolen." },
        { title: "Visa Information", description: "Details on visa requirements for Ethiopian citizens traveling abroad." },
    ];

    useEffect(() => {
        const scrollContainer = scrollRef.current;
        if (scrollContainer) {
            const scrollWidth = scrollContainer.scrollWidth;
            const animateScroll = () => {
                if (scrollContainer.scrollLeft >= scrollWidth / 2) {
                    scrollContainer.scrollLeft = 0;
                } else {
                    scrollContainer.scrollLeft += 1;
                }
            };
            const animationId = setInterval(animateScroll, 30);
            return () => clearInterval(animationId);
        }
    }, []);

    return (
        <section className="py-12 bg-gray-100 dark:bg-gray-800 overflow-hidden">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl mb-12">
                    Our Services
                </h2>
                <div className="relative">
                    <div ref={scrollRef} className="flex overflow-x-hidden">
                        <div className="flex animate-scroll">
                            {[...services, ...services].map((service, index) => (
                                <motion.div
                                    key={index}
                                    className="flex-shrink-0 w-64 mx-4 bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg"
                                >
                                    <div className="px-4 py-5 sm:p-6">
                                        <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                                            {service.title}
                                        </h3>
                                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-300">
                                            {service.description}
                                        </p>
                                    </div>
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
function ProcessSection() {
    const steps = [
        {
            title: "Submit Application",
            description:
                "Fill out the passport application form online or in person.",
        },
        {
            title: "Pay Fees",
            description: "Complete the payment for your passport application.",
        },
        {
            title: "Document Verification",
            description: "Officials will verify your submitted documents.",
        },
        {
            title: "Passport Production",
            description: "Your passport is produced and quality checked.",
        },
        {
            title: "Status Update",
            description: "Check your passport status online using our system.",
        },
        {
            title: "Collection",
            description:
                "Collect your passport from the designated office when ready.",
        },
    ];

    return (
        <section className="py-12 bg-white dark:bg-gray-900">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                    Passport Application Process
                </h2>
                <div className="mt-10">
                    <div className="relative">
                        {steps.map((step, index) => (
                            <motion.div
                                key={index}
                                initial={{ opacity: 0, x: -20 }}
                                animate={{ opacity: 1, x: 0 }}
                                transition={{
                                    duration: 0.5,
                                    delay: index * 0.1,
                                }}
                                className="relative pb-8"
                            >
                                <div className="relative flex items-start space-x-3">
                                    <div>
                                        <span className="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-900">
                                            <svg
                                                className="h-5 w-5 text-white"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth={2}
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        </span>
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                                            {step.title}
                                        </h3>
                                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-300">
                                            {step.description}
                                        </p>
                                    </div>
                                </div>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}

function FAQSection() {
    const [openIndex, setOpenIndex] = useState(null);
    const faqs = [
        {
            question: "How long does it take to get a passport in Ethiopia?",
            answer: "The processing time for an Ethiopian passport typically ranges from 2 to 8 weeks, depending on the type of service requested and current workload.",
        },
        {
            question: "What documents do I need for a passport application?",
            answer: "You'll need a completed application form, proof of citizenship (such as a birth certificate), valid ID, recent passport-sized photographs, and any additional documents specific to your situation.",
        },
        {
            question: "Can I check my passport status online?",
            answer: "Yes, you can check your passport status online using our system. You'll need your application number or other identifying information provided during the application process.",
        },
        {
            question: "How much does an Ethiopian passport cost?",
            answer: "The cost of an Ethiopian passport varies depending on the type of passport and processing speed. Please check our pricing section for the most up-to-date fees.",
        },
    ];

    return (
        <section className="py-12 bg-gray-100 dark:bg-gray-800">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl mb-8">
                    Frequently Asked Questions
                </h2>
                <div className="space-y-6">
                    {faqs.map((faq, index) => (
                        <motion.div
                            key={index}
                            className="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden"
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.5, delay: index * 0.1 }}
                        >
                            <button
                                className="w-full text-left px-6 py-4 focus:outline-none"
                                onClick={() =>
                                    setOpenIndex(
                                        openIndex === index ? null : index
                                    )
                                }
                            >
                                <div className="flex justify-between items-center">
                                    <h3 className="text-lg font-medium text-gray-900 dark:text-white">
                                        {faq.question}
                                    </h3>
                                    <svg
                                        className={`w-5 h-5 text-gray-500 transform ${
                                            openIndex === index
                                                ? "rotate-180"
                                                : ""
                                        }`}
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M19 9l-7 7-7-7"
                                        />
                                    </svg>
                                </div>
                            </button>
                            <motion.div
                                initial={{ height: 0 }}
                                animate={{
                                    height: openIndex === index ? "auto" : 0,
                                }}
                                transition={{ duration: 0.3 }}
                                className="overflow-hidden"
                            >
                                <p className="px-6 py-4 text-gray-600 dark:text-gray-300">
                                    {faq.answer}
                                </p>
                            </motion.div>
                        </motion.div>
                    ))}
                </div>
            </div>
        </section>
    );
}
function TestimonialsSection() {
    const scrollRef = useRef(null);
    const testimonials = [
        { name: "Abebe Kebede", quote: "The online status check saved me so much time. I knew exactly when my passport was ready for collection!" },
        { name: "Tigist Haile", quote: "The process was straightforward and the staff were very helpful. I got my passport faster than I expected." },
        { name: "Dawit Mengistu", quote: "As a frequent traveler, I appreciate how efficient the passport renewal process has become." },
        { name: "Frehiwot Tadesse", quote: "I was impressed by the user-friendly interface. It made checking my passport status a breeze." },
        { name: "Yohannes Gebre", quote: "The SMS notification feature is fantastic. I didn't have to keep checking the website for updates." },
        { name: "Meron Alemu", quote: "The customer support team was incredibly helpful when I had questions about my application." },
        { name: "Bereket Tadesse", quote: "I appreciate the transparency in the process. The timeline provided was accurate and helpful." },
    ];

    useEffect(() => {
        const scrollContainer = scrollRef.current;
        if (scrollContainer) {
            const scrollWidth = scrollContainer.scrollWidth;
            const animateScroll = () => {
                if (scrollContainer.scrollLeft >= scrollWidth / 2) {
                    scrollContainer.scrollLeft = 0;
                } else {
                    scrollContainer.scrollLeft += 1;
                }
            };
            const animationId = setInterval(animateScroll, 30);
            return () => clearInterval(animationId);
        }
    }, []);

    return (
        <section className="py-12 bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-blue-800 dark:to-indigo-900 overflow-hidden">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 className="text-3xl font-extrabold text-white sm:text-4xl text-center mb-12">
                    What Our Users Say
                </h2>
                <div className="relative">
                    <div ref={scrollRef} className="flex overflow-x-hidden">
                        <div className="flex animate-scroll">
                            {[...testimonials, ...testimonials].map((testimonial, index) => (
                                <motion.div
                                    key={index}
                                    className="flex-shrink-0 w-80 mx-4 bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6"
                                >
                                    <p className="text-gray-600 dark:text-gray-300 text-lg mb-4">"{testimonial.quote}"</p>
                                    <p className="text-indigo-600 dark:text-indigo-400 font-semibold">- {testimonial.name}</p>
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
