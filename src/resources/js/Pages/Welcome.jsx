import ApplicationLogo from "@/Components/ApplicationLogo";
import Footer from "@/Components/Footer";
// import PricingSection from "@/Components/PricingSection";
import { Link, Head } from "@inertiajs/react";
import { motion, useTransform, useSpring } from "framer-motion";
import { useState, useEffect, useRef } from "react";

export default function Welcome({ auth, passportCount }) {
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
            <div className="bg-gradient-to-r from-slate-100 to-slate-300 dark:from-slate-700 dark:to-zinc-900 dark:text-white/90 pb-8 min-h-screen w-full overflow-x-hidden">
                <img
                    id="background"
                    className="absolute -left-20 top-0 max-w-[1100px]  "
                    src="https://laravel.com/assets/img/welcome/background.svg"
                    alt="background image"
                />
                <div className="relative min-h-screen pt-4 px-1 pt-50 selection:bg-[#FF2D20] selection:text-white sm:px-2 lg:px-4">
                    {/* <div className="relative w-full max-w-2xl px-6 lg:max-w-7xl"> */}
                    <header className="flex flex-wrap justify-between items-center gap-2 px-1 py-2 lg:px-8">
                        <div className="mr-auto pt-2 w-20 sm:w-30 lg:w-50">
                            <ApplicationLogo className="w-full h-auto" />
                        </div>
                        <nav className="ml-auto flex justify-between space-x-2 lg:space-x-4 ">
                            <Link
                                href={route("blogs.index")}
                                className="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                            >
                                Blog
                            </Link>
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
                    <main className="bg-transparent w-full px-2 sm:px-6 lg:px-8 py-12 sm:py-20 rounded-xl">
                        <HeroSection auth={auth} value={passportCount} />
                        {/* <DashboardSection /> */}
                        <div className="my-20"></div>
                        <ServicesSection />
                        <ProcessSection />
                        {/* <PricingSection id="pricing" /> */}
                        <FAQSection />
                        <TestimonialsSection />
                        <Footer />
                    </main>
                </div>
            </div>
        </>
    );
}

function HeroSection({ auth, value }) {
    const [number, setNumber] = useState(0);
    const targetNumber = value;

    // Create a spring animation for smoother transition
    const springValue = useSpring(0, {
        stiffness: 75, // Controls how fast it reaches the target
        damping: 20, // Controls the bounciness
    });

    // Transform the spring value into an integer for display
    const animatedValue = useTransform(springValue, (latest) =>
        Math.floor(latest)
    );

    useEffect(() => {
        springValue.set(targetNumber); // Animate from 0 to targetNumber
    }, [springValue]);

    useEffect(() => {
        // Subscribe to the spring animation and update number
        const unsubscribe = animatedValue.on("change", (latest) => {
            setNumber(latest);
        });

        // Cleanup the subscription when component unmounts
        return () => unsubscribe();
    }, [animatedValue]);

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
                    <strong className="font-extrabold  sm:block text-blue-400">
                        Find Out Now!
                    </strong>
                </h1>

                <div className="flex justify-center items-center pt-2">
                    <p className="flex items-center">
                        From over
                        <span className="relative inline-block mx-2">
                            <span className="absolute inset-0 bg-gradient-to-r from-[#44BCFF] via-[#FF44EC] to-[#FF675E] rounded-xl blur-lg opacity-75"></span>
                            <motion.span
                                className="relative inline-block bg-red-400 text-white font-bold px-3 py-1 text-lg shadow-md cursor-pointer rounded-xl"
                                whileHover={{ scale: 1.1, rotate: 3 }}
                                transition={{
                                    type: "spring",
                                    stiffness: 400,
                                    damping: 10,
                                }}
                            >
                                {(Math.ceil(number / 10) * 10).toLocaleString()}
                                +
                            </motion.span>
                        </span>
                        Passports.
                    </p>
                </div>

                <h2 className="mt-4 sm:text-lg/relaxed">
                    Check the{" "}
                    <span className=" font-semibold text-blue-400">latest</span>{" "}
                    passport status published by the Ethiopian Immigration
                    Office!
                </h2>

                <div className="mt-8 flex flex-wrap justify-center gap-4">
                    <div class="relative inline-flex  group">
                        <div class="absolute transitiona-all duration-1000 opacity-70 -inset-px bg-gradient-to-r from-[#44BCFF] via-[#FF44EC] to-[#FF675E] rounded-xl blur-lg group-hover:opacity-100 group-hover:-inset-1 group-hover:duration-200 animate-tilt"></div>
                        <a
                            href={route("dashboard")}
                            title="Check Passport Status"
                            class="relative rounded inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white shadow focus:outline-none focus:ring active:bg-red-500 sm:w-auto transition ease-in-out delay-100 bg-blue-500 hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500 duration-300"
                            role="button"
                        >
                            {" "}
                            Check Your Passport Status
                        </a>
                    </div>

                    {/* <a
                        className="block w-full rounded px-12 py-3 text-sm font-medium text-red-600 bg-gray-100 shadow focus:outline-none focus:ring active:text-red-500 sm:w-auto transition ease-in-out delay-100 hover:-translate-y-1 hover:scale-110 hover:bg-red-700 hover:text-white duration-300"
                        href={route("login")}
                    >
                        {auth ? "Dashboard" : "Log In"}
                    </a> */}
                </div>
            </div>
        </motion.div>
    );
}

function ServicesSection() {
    const scrollRef = useRef(null);
    const services = [
        {
            title: "Passport Status Check",
            description:
                "Quickly check if your passport is ready for collection.",
        },
        {
            title: "Registration Information",
            description:
                "Learn how to register for a new passport or renew an existing one.",
        },
        {
            title: "Processing Time Updates",
            description:
                "Get the latest information on passport processing times.",
        },
        {
            title: "Passport Price Updates",
            description:
                "Get the latest information on how much a passport costs in Ethiopia.",
        },
        {
            title: "Document Requirements",
            description:
                "Find out what documents you need for your passport application.",
        },
        {
            title: "Online Application Assistance",
            description:
                "Step-by-step guidance for completing your online passport application.",
        },
        {
            title: "Expedited Service",
            description:
                "Information on how to expedite your passport processing for urgent travel needs.",
        },
        {
            title: "Lost Passport Support",
            description:
                "Guidance on what to do if your passport is lost or stolen.",
        },
        {
            title: "Visa Information",
            description:
                "Details on visa requirements for Ethiopian citizens traveling abroad.",
        },
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
                    <div
                        ref={scrollRef}
                        className="flex overflow-x-auto pb-4 hide-scrollbar"
                    >
                        <div className="flex animate-scroll">
                            {[...services, ...services].map(
                                (service, index) => (
                                    <motion.div
                                        key={index}
                                        className="flex-shrink-0 w-64 mx-4 bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg"
                                    >
                                        <div className="px-4 py-5 sm:p-6">
                                            <h2 className="text-lg font-medium text-gray-900 dark:text-white">
                                                {service.title}
                                            </h2>
                                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-300">
                                                {service.description}
                                            </p>
                                        </div>
                                    </motion.div>
                                )
                            )}
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
            description:
                "You must Complete the payment online through mobile banking or Telebirr within 3 hours of submitting the Application.",
        },
        {
            title: "Document Verification Appointment  ",
            description:
                "Officials will verify your submitted documents in person on your appointment day, take your thumbprint and will also take your passport picture that day.",
        },
        {
            title: "Passport Production",
            description:
                "Your passport is produced and quality checked after your appointment day.",
        },
        {
            title: "Status Update",
            description:
                "You can Check your passport status online using our system if it has been printed and is ready for pick up.",
        },
        {
            title: "Passport Collection",
            description:
                "Collect your passport from the designated office when ready.",
        },
    ];

    return (
        <section className="py-12 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-900">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                    Passport Application Process & Status Check
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
                                        <h2 className="text-lg font-medium text-gray-900 dark:text-white">
                                            {step.title}
                                        </h2>
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
            answer: "The processing time for an Ethiopian passport typically ranges from 1 to 2 months these days, depending on the type of service requested and current workload.",
        },
        {
            question: "How much does an Ethiopian passport cost?",
            answer: "The cost of an Ethiopian passport varies depending on the type of passport and processing speed. The range is from 5000 birr to 25,0000",
        },
        {
            question:
                "How long does it take to get an Urgent passport in Ethiopia?",
            answer: "The processing time for an Urgent Ethiopian passport typically ranges from 2 days to 5 days, depending on which service you paid for(25,000 birr or 20,000 birr), there are 3 tier from cheapest to most expensive.",
        },
        {
            question: "What documents do I need for a passport application?",
            answer: "You'll need a completed application form, proof of citizenship (such as a birth certificate), valid ID, recent passport-sized photographs, and any additional documents specific to your situation.",
        },
        {
            question: "Can I check my passport status online?",
            answer: "Yes, you can check your passport status online using our system. You'll need your application number or just using your full name and you can see when exactly you can pick up your passport at the latest.",
        },
        {
            question:
                "What documents do I need for a Lost/Stolen passport application?",
            answer: "You'll need a completed application form, proof of citizenship (such as a birth certificate), valid ID, Police evidence letter, Copy of passport or information about the passport if you have, with 13000 Birr, and any additional documents specific to your situation..",
        },
    ];

    return (
        <section className="py-12 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800">
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
        {
            name: "Abebe Kebede",
            quote: "The online status check saved me so much time. I knew exactly when my passport was ready for collection!",
        },
        {
            name: "Tigist Haile",
            quote: "The process was straightforward and the site wes very easy to use. I got my passport faster than I expected.",
        },
        {
            name: "Dawit Mengistu",
            quote: "As a frequent traveler, I appreciate how efficient the passport renewal process has become. and how easy it is to get informed here",
        },
        {
            name: "Frehiwot Tadesse",
            quote: "I was impressed by the user-friendly interface. It made checking my passport status a breeze.",
        },
        {
            name: "Yohannes Gebre",
            quote: "The SMS notification feature is fantastic. I didn't have to keep checking the website for updates.",
        },
        {
            name: "Meron Alemu",
            quote: "The customer support team on Telegram was incredibly helpful when I had questions about my application.",
        },
        {
            name: "Bereket Tadesse",
            quote: "I appreciate the transparency in the process. The timeline provided was accurate and helpful.",
        },
        {
            name: "Natnael Tadesse",
            quote: "I loved how easy it was to check my passport status. I appreciate the team's help and support.",
        },
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
        <section className="py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-blue-800 dark:to-indigo-900 overflow-hidden">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 className="text-3xl font-extrabold text-white sm:text-4xl text-center mb-12">
                    What Our Users Say
                </h2>
                <div className="relative">
                    <div
                        ref={scrollRef}
                        className="flex overflow-x-auto pb-4 hide-scrollbar"
                    >
                        <div className="flex animate-scroll">
                            {[...testimonials, ...testimonials].map(
                                (testimonial, index) => (
                                    <motion.div
                                        key={index}
                                        className="flex-shrink-0 w-80 mx-4 bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6"
                                    >
                                        <p className="text-gray-600 dark:text-gray-300 text-lg mb-4">
                                            "{testimonial.quote}"
                                        </p>
                                        <p className="text-indigo-600 dark:text-indigo-400 font-semibold">
                                            - {testimonial.name}
                                        </p>
                                    </motion.div>
                                )
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}

function DashboardSection() {
    return (
        <section className="py-12 bg-gray-100 dark:bg-gray-800">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="text-center">
                    <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white sm:text-4xl">
                        Access Your Dashboard
                    </h2>
                    <p className="mt-4 text-xl text-gray-600 dark:text-gray-300">
                        Check your passport status and manage your applications.
                    </p>
                    <div className="mt-8">
                        <Link
                            href={route("dashboard")}
                            className="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            Go to Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    );
}
