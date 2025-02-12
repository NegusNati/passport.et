import ApplicationLogo from "@/Components/ApplicationLogo";
import Footer from "@/Components/Footer";
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

    return (
        <>
            <Head>
                <title>
                    Fast & Reliable Ethiopian Passport Services Online | Check
                    Status & Apply - Passport.ET
                    {/* SEO Title: Clear, keyword-rich, brand focused */}
                </title>
                <meta
                    name="description"
                    content="Passport.ET: Your official online portal for Ethiopian passport services. Check your passport status instantly, apply for renewal, or request urgent passport processing. Get reliable information and assistance for all your passport needs in Ethiopia."
                />
            </Head>

            <div className="bg-gradient-to-r from-slate-100 to-slate-300 dark:from-slate-700 dark:to-zinc-900 dark:text-white/90 pb-8 min-h-screen w-full overflow-x-hidden">
                <img
                    id="background"
                    className="absolute -left-20 top-0 max-w-[1100px]  "
                    // src={asset("images/optimized-background.webp")} // Use optimized background image
                    src="https://laravel.com/assets/img/welcome/background.svg"
                    alt="background image"
                    loading="lazy" // Lazy loading for background
                />
                <div className="relative min-h-screen pt-4 px-1 pt-50 selection:bg-[#FF2D20] selection:text-white sm:px-2 lg:px-4">
                    <header className="flex flex-wrap justify-between items-center gap-2 px-1 py-2 lg:px-8">
                        <div className="mr-auto pt-2 w-20 sm:w-30 lg:w-50">
                            <ApplicationLogo className="w-full h-auto" />
                        </div>
                        <nav className="ml-auto flex justify-between space-x-2 lg:space-x-4 ">
                            <Link
                                href={route("blogs.index")}
                                className="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                            >
                                Articles
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
                        <div className="my-20"></div>
                        <ServicesSection />
                        <ProcessSection />
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

    const springValue = useSpring(0, {
        stiffness: 75,
        damping: 20,
    });

    const animatedValue = useTransform(springValue, (latest) =>
        Math.floor(latest)
    );

    useEffect(() => {
        springValue.set(targetNumber);
    }, [springValue]);

    useEffect(() => {
        const unsubscribe = animatedValue.on("change", (latest) => {
            setNumber(latest);
        });
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
                <h1 className="text-xl font-extrabold sm:text-3xl capitalize">
                    Need to Check Your Ethiopian Passport Status?
                    <strong className="font-extrabold  sm:block text-blue-400">
                        {" "}
                        Find Out Instantly Online!
                    </strong>
                    {/* H1: More direct question, includes "Ethiopian Passport Status" and action "Find Out Instantly Online!" */}
                </h1>

                <div className="flex justify-center items-center pt-2 text-xs">
                    <p className="flex items-center">
                        More than
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
                        Ethiopian Passports.
                        {/* Updated: Replaced "Passports" with "Ethiopians" to emphasize user trust */}
                    </p>
                </div>

                <h2 className="mt-2 text-xs sm:text-sm ">
                    Passport.ET is the leading online portal to{" "}
                    <span className=" font-semibold text-blue-400">
                        quickly check
                    </span>{" "}
                    your Ethiopian passport application status. Get the latest
                    updates directly from the Immigration and Citizenship
                    Service.
                    {/* H2:  Clearly states the purpose, includes "Ethiopian passport application status", "Immigration and Citizenship Service" */}
                </h2>

                <div className="mt-8 flex flex-wrap justify-center gap-4">
                    <div class="relative inline-flex  group">
                        <div class="absolute transitiona-all duration-1000 opacity-70 -inset-px bg-gradient-to-r from-[#44BCFF] via-[#FF44EC] to-[#FF675E] rounded-xl blur-lg group-hover:opacity-100 group-hover:-inset-1 group-hover:duration-200 animate-tilt"></div>
                        <a
                            href={route("dashboard")}
                            title="Check Passport Status Now" // Improved title attribute
                            class="relative rounded inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white shadow focus:outline-none focus:ring active:bg-red-500 sm:w-auto transition ease-in-out delay-100 bg-blue-500 hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500 duration-300"
                            role="button"
                        >
                            Check Passport Status Now {/* Stronger CTA text */}
                        </a>
                    </div>
                </div>
            </div>
        </motion.div>
    );
}

function ServicesSection() {
    const scrollRef = useRef(null);
    const services = [
        {
            title: "Ethiopian Passport Status Check Online", // Keyword-rich title
            description:
                "Effortlessly check your Ethiopian passport status online. Know when your passport is ready for collection without visiting the office.", // Keyword-rich description
        },
        {
            title: "Passport Renewal & Application Guidance", // Keyword-rich title
            description:
                "Step-by-step information on how to renew your Ethiopian passport or apply for a new one.  Understand the process and required documents.", // Keyword-rich description
        },
        {
            title: "Urgent/Expedited Passport Services", // Keyword-rich title
            description:
                "Need your passport fast? Learn about urgent and expedited Ethiopian passport application options and processing times.", // Keyword-rich description, includes "urgent/expedited"
        },
        {
            title: "Passport Price & Fee Updates (Ethiopia)", // Keyword-rich title
            description:
                "Stay informed on the latest Ethiopian passport prices and application fees. Get accurate cost information for different passport types.", // Keyword-rich description, includes "price & fees"
        },
        {
            title: "Complete Document Requirements Checklist", // Keyword-rich title
            description:
                "A detailed checklist of all necessary documents for your Ethiopian passport application. Ensure you have everything prepared.", // Keyword-rich description
        },
        {
            title: "Online Application Assistance & Support", // Keyword-rich title
            description:
                "Get help with your online Ethiopian passport application.  We provide guidance and support throughout the online process.", // Keyword-rich description
        },
        {
            title: "Ethiopian Visa Information for Travelers", // Keyword-rich title
            description:
                "Essential visa information for Ethiopian citizens traveling abroad. Understand visa requirements for different countries.", // Keyword-rich description, includes "visa information"
        },
        {
            title: "Lost or Stolen Passport Assistance", // Keyword-rich title
            description:
                "Guidance and steps to take if your Ethiopian passport is lost or stolen. Learn how to report and apply for a replacement.", // Keyword-rich description
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
                <h2 className="text-xl font-extrabold sm:text-3xl  text-gray-900 dark:text-white mb-8">
                    Explore Our Ethiopian Passport Services{" "}
                    {/* H2: More descriptive heading */}
                </h2>
                <p className="text-xs sm:text-sm text-gray-700 dark:text-gray-400 mb-8 ">
                    We offer a comprehensive suite of services to assist you
                    with every step of your Ethiopian passport journey. Whether
                    you need to check your status, renew your passport, or apply
                    urgently, we've got you covered.{" "}
                    {/* Added intro paragraph with keywords */}
                </p>
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
                                        className="flex-shrink-0 w-72 mx-4 bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg" // Increased width for better readability
                                    >
                                        <div className="px-4 py-5 sm:p-6">
                                            <h3 className="text-sm font-medium text-gray-900 dark:text-white">
                                                {service.title}{" "}
                                                {/* H3: Using H3 for service titles */}
                                            </h3>
                                            <p className="mt-1 text-xs text-gray-500 dark:text-gray-300">
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
            title: "1. Submit Your Ethiopian Passport Application", // Numbered steps for clarity
            description:
                "Begin your application by filling out the Ethiopian passport application form. You can apply online for convenience or visit an office in person.", // More descriptive step description
        },
        {
            title: "2. Securely Pay Passport Fees Online", // Numbered steps for clarity
            description:
                "Complete the payment for your Ethiopian passport application securely online via mobile banking or Telebirr within 3 hours of submission.", // More descriptive step description
        },
        {
            title: "3. Attend Document Verification Appointment", // Numbered steps for clarity
            description:
                "Visit the designated immigration office on your appointment day for document verification. Officials will also capture your biometrics and passport photo.", // More descriptive step description
        },
        {
            title: "4. Passport Production & Quality Check", // Numbered steps for clarity
            description:
                "Your Ethiopian passport enters the production phase, including printing and a thorough quality assurance check after your appointment.", // More descriptive step description
        },
        {
            title: "5. Track Your Passport Status Online", // Numbered steps for clarity
            description:
                "Utilize our online system to track your Ethiopian passport status.  Receive updates on printing and readiness for pickup.", // More descriptive step description
        },
        {
            title: "6. Collect Your New Ethiopian Passport", // Numbered steps for clarity
            description:
                "Once your status indicates 'Ready for Collection,' visit the designated office to collect your new Ethiopian passport.", // More descriptive step description
        },
    ];

    return (
        <section className="py-12 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-900">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 className="text-xl font-extrabold sm:text-3xl  text-gray-900 dark:text-white ">
                    Ethiopian Passport Application Process & Status Check -
                    Simplified {/* H2: More user-friendly heading */}
                </h2>
                <p className="mt-4 text-xs sm:text-sm text-gray-700 dark:text-gray-400">
                    Understanding the Ethiopian passport application process is
                    easy with Passport.ET. Follow these simple steps to apply
                    and track your passport status. {/* Intro paragraph */}
                </p>
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
                                            {step.title}{" "}
                                            {/* H3: Using H3 for step titles */}
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
            question:
                "What is the standard processing time for an Ethiopian passport?", // More specific question
            answer: "Typically, Ethiopian passport processing takes 1 to 2 months, but this can vary based on service type and current application volumes.",
        },
        {
            question: "How much does an Ethiopian passport application cost?", // More specific question
            answer: "The cost of an Ethiopian passport varies, ranging from 5000 to 25,000 Birr depending on the passport type and processing speed chosen.",
        },
        {
            question: "How quickly can I get an urgent Ethiopian passport?", // More specific question, includes "urgent"
            answer: "Urgent Ethiopian passport processing usually takes 2 to 5 days, depending on the specific urgent service tier selected (prices vary from 20,000 to 25,000 Birr).", // Includes "urgent" and price range
        },
        {
            question:
                "What documents are required for Ethiopian passport application?", // More specific question
            answer: "You'll need a completed application form, proof of Ethiopian citizenship (like a birth certificate), valid identification, recent photos, and possibly other documents based on your situation.",
        },
        {
            question:
                "Can I track my Ethiopian passport application status online?", // More specific question
            answer: "Yes, easily track your Ethiopian passport status online through our system. Use your application number or full name to get the latest updates and expected pickup date.", // Includes "track status online"
        },
        {
            question:
                "What should I do if my Ethiopian passport is lost or stolen?", // More specific question, includes "lost/stolen"
            answer: "If your Ethiopian passport is lost or stolen, you'll need to complete a new application, provide proof of citizenship, ID, a police report, and pay a replacement fee of 13,000 Birr. Additional documents might be needed.", // Includes "lost/stolen" process and fee
        },
    ];

    return (
        <section className="py-12 px-4 sm:px-6 lg:px-8 bg-white dark:bg-gray-800">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 className="text-xl font-extrabold sm:text-3xl  text-gray-900 dark:text-white  mb-8">
                    Frequently Asked Questions about Ethiopian Passports{" "}
                    {/* H2: Clear heading */}
                </h2>
                <p className="text-xs sm:text-sm text-gray-700 dark:text-gray-400 mb-8 text-center">
                    Find quick answers to common questions about Ethiopian
                    passport applications, renewals, urgent services, and status
                    checks. {/* Intro paragraph */}
                </p>
                <div className="space-y-6">
                    <script type="application/ld+json">
                        {JSON.stringify({
                            "@context": "https://schema.org",
                            "@type": "FAQPage",
                            mainEntity: faqs.map((faq) => ({
                                "@type": "Question",
                                name: faq.question,
                                acceptedAnswer: {
                                    "@type": "Answer",
                                    text: faq.answer,
                                },
                            })),
                        })}
                    </script>
                    {/* FAQ Schema Markup added here */}
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
                                    <h3 className="text-sm font-medium text-gray-900 dark:text-white">
                                        {faq.question}{" "}
                                        {/* H3: Using H3 for FAQ questions */}
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
                                <p className="px-6 py-4 text-gray-600 dark:text-gray-300 text-xs">
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
            quote: "The process was straightforward and the site was very easy to use. I got my passport faster than I expected.",
        },
        {
            name: "Dawit Mengistu",
            quote: "As a frequent traveler, I appreciate how efficient the passport renewal process has become and how easy it is to get informed here.",
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
        <section className="py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-blue-400 to-blue-500 dark:from-blue-800 dark:to-indigo-900 overflow-hidden">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 className="text-xl font-extrabold sm:text-3xl  text-white text-center mb-12">
                    What Our Users Say About Passport.ET{" "}
                    {/* H2: Brand mention in heading */}
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
                                        <p className="text-gray-600 dark:text-gray-300 text-xs mb-4">
                                            "{testimonial.quote}"
                                        </p>
                                        <p className="text-indigo-600 dark:text-indigo-400 font-semibold text-xs">
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
                        Access Your Passport Dashboard{" "}
                        {/* H2: More specific heading */}
                    </h2>
                    <p className="mt-4 text-xl text-gray-600 dark:text-gray-300">
                        Quickly check your Ethiopian passport status and manage
                        your applications in your personal dashboard.{" "}
                        {/* More descriptive paragraph */}
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
