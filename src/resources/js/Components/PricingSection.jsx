import React from "react";
import { CheckIcon } from "@heroicons/react/20/solid";

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
        <section
            className="bg-white dark:bg-gradient-to-b from-slate-600 to-slate-300  py-16 sm:py-24 rounded-xl pb-10 dark:text-white/90  "
            id="pricing"
        >
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-4xl text-center">
                    <h2 className="text-base font-semibold leading-7 text-indigo-600 dark:text-white tracking-wider">
                        Pricing
                    </h2>
                    <p className="mt-2 text-4xl font-bold tracking-tight text-gray-900  sm:text-5xl">
                        Choose the right plan for You
                    </p>
                </div>
                <p className="mx-auto mt-6 max-w-2xl text-center text-lg leading-8 text-gray-600 dark:text-gray-700 ">
                    Choose an affordable plan that's packed with the best
                    features for accesing the latest inpormation about
                    Passports, delivery date of specific passports and more.
                </p>
                {/* <div className="mt-16 grid grid-cols-1 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 lg:gap-x-8 lg:gap-y-0 "> */}
                <div className="mt-16 flex justify-center">
                    {tiers.map((tier) => (
                        <div
                            key={tier.name}
                            className="border border-gray-200 rounded-2xl p-8 shadow-sm  "
                        >
                            <h3 className="text-2xl font-semibold leading-8 text-gray-900 dark:text-white ">
                                {tier.name}
                            </h3>
                            <p className="mt-4 text-sm leading-6 text-gray-600 dark:text-white/90 ">
                                {tier.description}
                            </p>
                            <p className="mt-6 flex items-baseline gap-x-1">
                                <span className="text-4xl sm:text-6xl font-bold tracking-tight text-gray-900">

                                   {tier.price === "Free"
                                        ? "Free"
                                        : tier.price}
                                </span>

                                <span className="text-sm font-semibold leading-6 text-gray-600">
                                    {tier.frequency}
                                </span>
                            </p>
                            <p className="mt-1 flex items-baseline gap-x-1 line-through  ">
                            <span className="text-blue-900 font-semibold">50</span> Birr per month
                            </p>
                            <ul
                                role="list"
                                className="mt-8 space-y-3 text-sm leading-6 text-gray-600"
                            >
                                {tier.features.map((feature) => (
                                    <li
                                        key={feature}
                                        className="flex gap-x-3 capitalize"
                                    >
                                        <CheckIcon
                                            className="h-6 w-5 flex-none text-indigo-600"
                                            aria-hidden="true"
                                        />
                                        {feature}
                                    </li>
                                ))}
                            </ul>
                            {tier.price == "Free" ? (
                                <a
                                    href={route("register")}
                                    className="mt-10 mb-auto mx-4  block rounded-md px-3.5 py-2 text-center text-sm font-semibold leading-6 text-white shadow-sm  focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 hover:ring-indigo-700 focus:outline-none focus:ring active:text-indigo-500  transition ease-in-out delay-100 bg-blue-500 hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500 duration-300"
                                >
                                    {tier.cta}
                                </a>
                            ) : (
                                    <a
                                        href={route("register", {
                                            amount: tier.price,
                                        })}
                                        className="mt-10 mb-auto mx-4  block rounded-md px-3.5 py-2 text-center text-sm font-semibold leading-6 text-white shadow-sm  focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 hover:ring-indigo-700 focus:outline-none focus:ring active:text-indigo-500  transition ease-in-out delay-100 bg-blue-500 hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500 duration-300"
                                    >
                                        {tier.cta}
                                    </a>
                            )}
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
