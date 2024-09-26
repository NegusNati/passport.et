import DismissibleBanner from "@/Components/DismissibleBanner";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";
import { Head, Link } from "@inertiajs/react";
import { React, useState } from "react";

export default function Index({ auth, cities }) {
    console.log("cities", cities);

    const [idValue, setIdValue] = useState("");
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
    // console.log(idValue);
    return (
        <AuthGuestLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-lg sm:text-xl  text-gray-800 dark:text-gray-200 leading-tight">
                    Locations
                </h2>
            }
        >
            <Head title="Locations" />
            <DismissibleBanner
                text="Welcome to PassportET, your one stop solution for checking your passport status"
                bgColor={"bg-indigo-400"}
            />

            <div className="pt-12 py-12 ">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 ">
                    <div className="bg-gradient-to-r from-slate-500 to-slate-100 dark:from-slate-700 dark:to-zinc-900 dark:text-white/50 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100 space-y-6">
                            {cities.map((city, index) => (
                                <Link
                                    key={index}
                                    href={route('passport.by-location', { location: city.location })}
                                    className="block mb-4 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition duration-300"
                                >
                                    <h3 className="text-lg font-semibold">
                                        {city.location}
                                    </h3>
                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                        View all passports for this location
                                    </p>
                                </Link>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AuthGuestLayout>
    );
}
