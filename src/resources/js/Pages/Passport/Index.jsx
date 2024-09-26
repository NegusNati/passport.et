import { useEffect, useState } from "react";

import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import Authenticated from "@/Layouts/AuthenticatedLayout";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import InputError from "@/Components/InputError";

function Index({ auth }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        requestNumber: "",
        firstName: "",
        middleName: "",
        lastName: "",
    });
    const [showRequestNumber, setShowRequestNumber] = useState(false);

    useEffect(() => {
        return () => {
            reset("password", "password_confirmation");
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();

        post(route("passport.show"));
    };
    return (
        <AuthGuestLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight selection:bg-[#FF2D20] selection:text-white capitalize">
                    search for your passport
                </h2>
            }
        >
            <Head title="Find Passport" />

            <main className="px-4 pt-6 max-[990px]:mx-auto max-[990px]:mt-6 selection:bg-[#FF2D20] selection:text-white ">
                <form
                    onSubmit={submit}
                    className="py-12 px-2 sm:px-6 lg:px-8   "
                >
                      <div className=" my-4 mx-2">
                        <p>Input Your Name</p>
                    </div>
                    <div className="  sm:px-2 flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] md:row-span-3 lg:p-10 lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
                        <div className="mt-4">
                            <InputLabel
                                htmlFor="firstName"
                                value="First Name"
                            />

                            <TextInput
                                id="firstName"
                                name="firstName"
                                value={data.firstName}
                                className="mt-1 block w-full"
                                autoComplete="firstName"
                                placeholder="Natnael"
                                onChange={(e) =>
                                    setData("firstName", e.target.value)
                                }
                            />

                            <InputError
                                message={errors.firstName}
                                className="mt-2"
                            />
                        </div>
                        <div className="mt-4">
                            <InputLabel
                                htmlFor="middleName"
                                value="Middle Name/Father's Name"
                            />

                            <TextInput
                                id="middleName"
                                name="middleName"
                                value={data.middleName}
                                className="mt-1 block w-full"
                                autoComplete="middleName"
                                placeholder="Birhanu"
                                onChange={(e) =>
                                    setData("middleName", e.target.value)
                                }
                            />

                            <InputError
                                message={errors.middleName}
                                className="mt-2"
                            />
                        </div>
                        <div className="mt-4">
                            <InputLabel
                                htmlFor="lastName"
                                value="Last Name/Grandfather's Name"
                            />

                            <TextInput
                                id="lastName"
                                name="lastName"
                                value={data.lastName}
                                className="mt-1 block w-full"
                                autoComplete="lastName"
                                placeholder="Gezahegn"
                                onChange={(e) =>
                                    setData("lastName", e.target.value)
                                }
                            />

                            <InputError
                                message={errors.lastName}
                                className="mt-2"
                            />
                        </div>
                    </div>
                    {/* <div className=" my-4 mx-2">
                        <p>Or Find By Request Number</p>
                    </div> */}

                    <button
                        type="button"
                        onClick={() => setShowRequestNumber(!showRequestNumber)}
                        className="my-4 p-2 rounded-xl bg-blue-500 hover:bg-blue-600 transition-colors duration-300 group"
                    >
                        {showRequestNumber
                            ? "Search by Name"
                            : "Find by Request Number"}
                    </button>
                    {showRequestNumber ? (
                        <div className="flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] md:row-span-3 lg:p-10 lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20] animate-slide-in">
                            <InputLabel
                                htmlFor="requestNumber"
                                value="Request Number"
                            />

                            <TextInput
                                id="requestNumber"
                                name="requestNumber"
                                value={data.requestNumber}
                                className="mt-1 block w-full"
                                autoComplete="requestNumber"
                                isFocused={true}
                                placeholder="AAL3912660"
                                onChange={(e) =>
                                    setData("requestNumber", e.target.value)
                                }
                            />

                            <InputError
                                message={errors.requestNumber}
                                className="mt-2"
                            />
                        </div>
                    ) : (
                        ""
                    )}
                    <div className="flex items-center justify-end mt-4">
                        <PrimaryButton className="mt-4 transition ease-in-out delay-100 bg-blue-500 hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500 duration-100">
                            Find Passport
                        </PrimaryButton>
                    </div>
                </form>
            </main>
        </AuthGuestLayout>
    );
}

export default Index;
