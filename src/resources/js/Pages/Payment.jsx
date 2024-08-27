import TextInput from "@/Components/TextInput";
import React, { useState } from "react";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import GuestLayout from "@/Layouts/GuestLayout";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import Footer from "@/Components/Footer";

function Payment() {
    const { props } = usePage();
    const { amount } = props;

    const { data, setData, post, processing, errors, reset } = useForm({
        firstName: "",
        lastName: "",
        phoneNumber: "",
        amount: amount,
        plan: "premium",
    });

    const handleSubmit = (e) => {
        e.preventDefault();

        post(route("pay"));
    };

    return (
        <>
            <main className=" min-h-screen bg-gradient-to-r from-slate-100 to-slate-300 dark:from-slate-700 dark:to-zinc-900 dark:text-white/90 m-auto selection:bg-[#FF2D20] selection:text-white space-y-4 pt-24">
                <div className="max-w-md mx-auto p-4  pb-6 mb-32 bg-white rounded shadow-md dark:bg-gray-800   ">
                    <Head title="Premium" />
                    <div className=" py-4">
                        <h2 className="text-2xl font-bold mb-4 text-center uppercase pb-4  ">
                            Payment Information
                        </h2>
                        <form
                            onSubmit={handleSubmit}
                            className="space-y-4 mt-18"
                        >
                            <div>
                                <InputLabel
                                    htmlFor="firstName"
                                    value="First Name:"
                                />

                                <TextInput
                                    type="text"
                                    id="firstName"
                                    value={data.firstName}
                                    onChange={(e) =>
                                        setData("firstName", e.target.value)
                                    }
                                    required
                                    autoComplete="firstName"
                                    isFocused={true}
                                    name="firstName"
                                    className="mt-1 block w-full"
                                    placeholder="Natnael"
                                />

                                <InputError
                                    message={errors.firstName}
                                    className="mt-2"
                                />
                            </div>
                            <div>
                                <InputLabel
                                    htmlFor="lastName"
                                    value="Last Name:"
                                />

                                <TextInput
                                    type="text"
                                    id="lastName"
                                    value={data.lastName}
                                    onChange={(e) =>
                                        setData("lastName", e.target.value)
                                    }
                                    required
                                    autoComplete="lastName"
                                    className="mt-1 block w-full"
                                    name="lastName"
                                    placeholder="Birhanu"
                                />

                                <InputError
                                    message={errors.lastName}
                                    className="mt-2"
                                />
                            </div>
                            <div>
                                <InputLabel
                                    htmlFor="phoneNumber"
                                    value="Phone Number:"
                                />

                                <TextInput
                                    type="text"
                                    id="phoneNumber"
                                    value={data.phoneNumber}
                                    onChange={(e) =>
                                        setData("phoneNumber", e.target.value)
                                    }
                                    required
                                    autoComplete="phoneNumber"
                                    className="mt-1 block w-full"
                                    name="phoneNumber"
                                    placeholder="09123456789"
                                />

                                <InputError
                                    message={errors.phoneNumber}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="amount"
                                    value="Payment in Birr:"
                                />

                                <TextInput
                                    type="text"
                                    id="amount"
                                    value={data.amount}
                                    // onChange={(e) =>
                                    //     setData("amount", e.target.value)
                                    // }
                                    required
                                    autoComplete="amount"
                                    className="mt-1 block w-full"
                                    disabled
                                    name="amount"
                                    placeholder="60"
                                />

                                <InputError
                                    message={errors.amount}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="premium"
                                    value="Premium Plan:"
                                />

                                <TextInput
                                    type="radio"
                                    id="premium"
                                    value={data.plan}
                                    // onChange={(e) => setData("plan", e.target.value)}
                                    isFocused={true}
                                    name="plan"
                                    checked={data.plan === "premium"}
                                />

                                <InputError
                                    message={errors.plan}
                                    className="mt-2"
                                />
                            </div>
                            {/* <label
                            htmlFor="lastName"
                            className="relative block rounded-md border border-gray-200 shadow-sm focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600"
                        >
                            <input
                                type="text"
                                id="lastName"
                                value={lastName}
                                onChange={(e) => setLastName(e.target.value)}
                                required
                                className="peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0"
                                placeholder="lastName"
                            />

                            <span className="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
                                Last Name:
                            </span>
                        </label>

                        <br />
                        <label
                            htmlFor="phoneNumber"
                            className="relative block rounded-md border border-gray-200 shadow-sm focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600"
                        >
                            <input
                                type="text"
                                id="phoneNumber"
                                value={phoneNumber}
                                onChange={(e) => setPhoneNumber(e.target.value)}
                                required
                                className="peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0"
                                placeholder="phoneNumber"
                            />

                            <span className="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
                                Phone Number:
                            </span>
                        </label>
                        <br />
                        <label
                            htmlFor="amount"
                            className="relative block rounded-md border border-gray-200 shadow-sm focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600"
                        >
                            <input
                                type="text"
                                id="amount"
                                value={amount}
                                required
                                className="peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0"
                                placeholder="amount"
                            />

                            <span className="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
                                Payment in Birr:
                            </span>
                        </label> */}
                            <br />
                            <button
                                type="submit"
                                className="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded flex ml-auto"
                            >
                                Submit Payment
                            </button>
                        </form>
                    </div>
                </div>
                <Footer />
            </main>
        </>
    );
}

export default Payment;
