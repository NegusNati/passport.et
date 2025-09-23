import { useState } from "react";

import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";
import { Head } from "@inertiajs/react";
import { fetchPassports } from "@/api/passports";

function Index({ auth }) {
    const [form, setForm] = useState({
        first_name: "",
        middle_name: "",
        last_name: "",
        request_number: "",
    });
    const [showRequestNumber, setShowRequestNumber] = useState(false);
    const [results, setResults] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const handleChange = (event) => {
        const { name, value } = event.target;
        setForm((prev) => ({ ...prev, [name]: value }));
    };

    const submit = async (event) => {
        event.preventDefault();
        setLoading(true);
        setError(null);

        const payload = showRequestNumber
            ? { request_number: form.request_number }
            : {
                  first_name: form.first_name,
                  middle_name: form.middle_name,
                  last_name: form.last_name,
              };

        const sanitized = Object.entries(payload).reduce((acc, [key, value]) => {
            if (value && value.trim() !== "") {
                acc[key] = value.trim();
            }
            return acc;
        }, {});

        if (Object.keys(sanitized).length === 0) {
            setError("Please enter at least one search term.");
            setLoading(false);
            return;
        }

        try {
            const response = await fetchPassports({ ...sanitized, limit: 25 });
            setResults(response.data ?? []);
        } catch (err) {
            setError(
                err?.response?.data?.message || err.message || "Unable to fetch passports."
            );
        } finally {
            setLoading(false);
        }
    };

    return (
        <AuthGuestLayout
            user={auth.user}
            header={
                <h1 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight selection:bg-[#FF2D20] selection:text-white capitalize">
                    search for your passport
                </h1>
            }
        >
            <Head title="Find Passport" />

            <main className="px-4 pt-6 max-w-[990px] mx-auto selection:bg-[#FF2D20] selection:text-white">
                <form onSubmit={submit} className="py-12 px-2 sm:px-6 lg:px-8">
                    <div className="my-4 mx-2">
                        <p>Input Your Name</p>
                    </div>
                    <div className="sm:px-2 flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-6 shadow ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700">
                        {!showRequestNumber && (
                            <>
                                <div className="mt-4 w-full">
                                    <InputLabel htmlFor="first_name" value="First Name" />

                                    <TextInput
                                        id="first_name"
                                        name="first_name"
                                        value={form.first_name}
                                        className="mt-1 block w-full"
                                        autoComplete="given-name"
                                        placeholder="Natnael"
                                        onChange={handleChange}
                                        isFocused
                                    />
                                </div>
                                <div className="mt-4 w-full">
                                    <InputLabel htmlFor="middle_name" value="Middle Name / Father's Name" />

                                    <TextInput
                                        id="middle_name"
                                        name="middle_name"
                                        value={form.middle_name}
                                        className="mt-1 block w-full"
                                        autoComplete="additional-name"
                                        placeholder="Birhanu"
                                        onChange={handleChange}
                                    />
                                </div>
                                <div className="mt-4 w-full">
                                    <InputLabel htmlFor="last_name" value="Last Name / Grandfather's Name" />

                                    <TextInput
                                        id="last_name"
                                        name="last_name"
                                        value={form.last_name}
                                        className="mt-1 block w-full"
                                        autoComplete="family-name"
                                        placeholder="Gezahegn"
                                        onChange={handleChange}
                                    />
                                </div>
                            </>
                        )}
                        {showRequestNumber && (
                            <div className="mt-4 w-full">
                                <InputLabel htmlFor="request_number" value="Request Number" />

                                <TextInput
                                    id="request_number"
                                    name="request_number"
                                    value={form.request_number}
                                    className="mt-1 block w-full"
                                    autoComplete="off"
                                    placeholder="AAL3912660"
                                    onChange={handleChange}
                                    isFocused
                                />
                            </div>
                        )}
                    </div>

                    <button
                        type="button"
                        onClick={() => setShowRequestNumber((prev) => !prev)}
                        className="my-4 p-2 rounded-xl hover:bg-white hover:text-black transition duration-300"
                    >
                        {showRequestNumber ? "Search By Name" : "Or Find By Request Number"}
                    </button>

                    <PrimaryButton className="mt-4" disabled={loading}>
                        {loading ? "Searching..." : "Search"}
                    </PrimaryButton>
                </form>

                {error && (
                    <div className="mb-6 rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-700">
                        {error}
                    </div>
                )}

                {results.length > 0 && (
                    <div className="overflow-x-auto rounded-lg bg-white p-6 shadow ring-1 ring-black/5 dark:bg-zinc-900 dark:text-white/80">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Request #
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Name
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Location
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Publish Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {results.map((passport) => (
                                    <tr key={passport.id} className="hover:bg-gray-100 transition">
                                        <td className="px-4 py-3 font-medium text-gray-900">
                                            {passport.request_number ?? passport.requestNumber}
                                        </td>
                                        <td className="px-4 py-3 text-gray-700">
                                            {[passport.first_name ?? passport.firstName, passport.middle_name ?? passport.middleName, passport.last_name ?? passport.lastName]
                                                .filter(Boolean)
                                                .join(" ")}
                                        </td>
                                        <td className="px-4 py-3 text-gray-700">
                                            {passport.location}
                                        </td>
                                        <td className="px-4 py-3 text-gray-700">
                                            {passport.date_of_publish ?? passport.dateOfPublish}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </main>
        </AuthGuestLayout>
    );
}

export default Index;
