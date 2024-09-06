import TextInput from "@/Components/TextInput";
import React, { useState } from "react";
import { Head, Link, useForm, usePage } from "@inertiajs/react";
import GuestLayout from "@/Layouts/GuestLayout";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";

function Subscribe() {
    const { data, setData, post, processing, errors, reset } = useForm({
        plan: "",
    });

    const handleSubmit = (e) => {
        e.preventDefault();

        post(route("subscribe"));
    };

    return (
        <main className=" min-h-screen  bg-gray-100 dark:bg-gray-900  bg-gradient-to-r from-rose-100 to-teal-100 pt-20 pb-20">
            <div className="max-w-md mx-auto p-4 pt-6 pb-6 mb-4 bg-white rounded shadow-md dark:bg-gray-800">

                <h1 className="text-2xl font-bold mb-4 text-center uppercase pb-4  ">Choose Your Plan</h1>


                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <InputLabel htmlFor="premium" value="Premium Plan:" />

                        <TextInput
                            type="radio"
                            id="premium"
                            value="premium"
                            onChange={(e) => setData("plan", e.target.value)}
                            isFocused={true}
                            name="plan"

                        />

                        <InputError message={errors.plan} className="mt-2" />
                    </div>
                    <div>
                        <InputLabel htmlFor="free" value="Free Plan:" />

                        <TextInput
                            type="radio"
                            id="free"
                            value="free"
                            onChange={(e) => setData("plan", e.target.value)}

                            name="plan"
                        />

                        <InputError message={errors.plan} className="mt-2" />
                    </div>
                    <br />
                    <button
                        type="submit"
                        className="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Submit Payment
                    </button>
                </form>
            </div>
        </main>
    );
}

export default Subscribe;
