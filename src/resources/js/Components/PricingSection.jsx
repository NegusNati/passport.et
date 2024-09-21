import React from "react";
import { CheckIcon } from "@heroicons/react/20/solid";
import { motion } from "framer-motion";

const tiers = [
    {
        name: "Basic",
        price: "Free",
        frequency: "/For limited time",
        description: "Perfect for checking few time a day.",
        features: [
            "Latest passport information",
            "120 queries per hour",
            "passport delivery date & time",
            "Detailed information",
            "Help center access",
            "Telegram support",
        ],
        cta: "Start Now",
    },
    // {
    //     name: "Premium",
    //     price: 20,
    //     frequency: "Birr/month",
    //     description: "Ideal for frequent Passport checking.",
    //     features: [
    //         "Latest passport information",
    //         "1000 queries per hour",
    //         "passport delivery date & time",
    //         "Detailed information",
    //         "Help center access",
    //         "Priority email support",
    //     ],
    //     cta: "Start Now",
    // },
    // {
    //     name: "Premium Plus",
    //     price: 60,
    //     frequency: "Birr/month",
    //     description: "For Internet Cafe & Passport Registers.",
    //     features: [
    //         "Latest passport information",
    //         "Unlimited queries per hour",
    //         "Unlimited use of the system",
    //         "passport delivery date & time",
    //         "Detailed information",
    //         "Help center access",
    //         "Priority email support",
    //         "24/7 Priority support",
    //         "Dedicated account manager",
    //         "Custom integrations",
    //     ],
    //     cta: "Start Now",
    // },
];

export default function PricingSection() {
    return (
        <section className="bg-gradient-to-b from-white to-gray-100 dark:from-gray-900 dark:to-gray-800 py-16 sm:py-24 ">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <motion.div
                    className="mx-auto max-w-4xl text-center"
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.8 }}
                >
                    <h2 className="text-base font-semibold leading-7 text-indigo-600 dark:text-indigo-400">
                        Pricing
                    </h2>
                    <p className="mt-2 text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">
                        Choose the right plan for You
                    </p>
                    <p className="mx-auto mt-6 max-w-2xl text-center text-lg leading-8 text-gray-600 dark:text-gray-300">
                        Choose an affordable plan that's packed with the best
                        features for accessing the latest information about
                        Passports, delivery dates, and more.
                    </p>
                </motion.div>
                <motion.div
                    className="mt-16 flex justify-center"
                    initial={{ opacity: 0, scale: 0.9 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ duration: 0.8, delay: 0.2 }}
                >
                    {tiers.map((tier) => (
                        <div
                            key={tier.name}
                            className="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl p-8 shadow-lg transform hover:scale-105 transition duration-300"
                            id="pricing"
                        >
                            <h3 className="text-2xl font-semibold leading-8 text-gray-900 dark:text-white">
                                {tier.name}
                            </h3>
                            <p className="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                {tier.description}
                            </p>
                            <p className="mt-6 flex items-baseline gap-x-1">
                                <span className="text-5xl font-bold tracking-tight text-indigo-600 dark:text-indigo-400">
                                    {tier.price === "Free"
                                        ? "Free"
                                        : tier.price}
                                </span>
                                <span className="text-sm font-semibold leading-6 text-gray-600 dark:text-gray-400">
                                    {tier.frequency}
                                </span>
                            </p>
                            <p className="mt-1 flex items-baseline gap-x-1 line-through text-gray-500 dark:text-gray-400">
                                <span className="text-blue-900 dark:text-blue-400 font-semibold">
                                    50
                                </span>{" "}
                                Birr per month
                            </p>
                            <ul
                                role="list"
                                className="mt-8 space-y-3 text-sm leading-6 text-gray-600 dark:text-gray-300"
                            >
                                {tier.features.map((feature) => (
                                    <li
                                        key={feature}
                                        className="flex gap-x-3 items-center"
                                    >
                                        <CheckIcon
                                            className="h-6 w-5 flex-none text-indigo-600 dark:text-indigo-400"
                                            aria-hidden="true"
                                        />
                                        <span className="capitalize">
                                            {feature}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                            <a
                                href={route("register")}
                                className="mt-10 block w-full rounded-md bg-indigo-600 px-3.5 py-2 text-center text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:w-auto transition ease-in-out delay-100  hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500 duration-300"
                                id="pricing"
                            >
                                {tier.cta}
                            </a>
                        </div>
                    ))}
                </motion.div>
            </div>
        </section>
    );
}
