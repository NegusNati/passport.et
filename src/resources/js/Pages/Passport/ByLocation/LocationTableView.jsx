import AuthGuestLayout from "@/Layouts/AuthGuestLayout";
import { Head, Link } from "@inertiajs/react";
import formatDate from "@/helpers/formarDate";
import React, { useCallback, useEffect, useMemo, useState } from "react";
import { fetchPassports } from "@/api/passports";

const sanitizeParams = (params) =>
    Object.entries(params).reduce((acc, [key, value]) => {
        if (value !== null && value !== undefined && value !== "") {
            acc[key] = value;
        }
        return acc;
    }, {});

function LocationTableView({ auth, location }) {
    const [passports, setPassports] = useState({
        data: [],
        links: {},
        meta: {},
    });
    const [page, setPage] = useState(1);
    const [perPage, setPerPage] = useState(25);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const loadPassports = useCallback(
        async (overrides = {}) => {
            setLoading(true);
            setError(null);
            try {
                const params = sanitizeParams({
                    location,
                    per_page: overrides.perPage ?? perPage,
                    page: overrides.page ?? page,
                });

                const response = await fetchPassports(params);
                setPassports({
                    data: response.data ?? [],
                    links: response.links ?? {},
                    meta: response.meta ?? {},
                });
                if (response.meta?.current_page) {
                    setPage(response.meta.current_page);
                }
            } catch (err) {
                setError(
                    err?.response?.data?.message || err.message || "Unable to load passports for this location."
                );
            } finally {
                setLoading(false);
            }
        },
        [location, page, perPage]
    );

    useEffect(() => {
        loadPassports();
    }, [loadPassports]);

    const paginationMeta = useMemo(() => passports.meta || {}, [passports.meta]);
    const paginationLinks = useMemo(() => passports.links || {}, [passports.links]);
    const passportRows = passports.data || [];

    const handlePageChange = (nextPage) => {
        setPage(nextPage);
        loadPassports({ page: nextPage });
    };

    const handlePerPageChange = (event) => {
        const value = Number(event.target.value);
        setPerPage(value);
        setPage(1);
        loadPassports({ page: 1, perPage: value });
    };

    return (
        <AuthGuestLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight py-4 capitalize">
                    Daily updated passports {location ? `for ${location}` : ""}
                </h2>
            }
        >
            <Head title={`Passports | ${location}`} />
            <main className="max-w-[990px] m-auto mb-20 bg-gray-200 rounded-2xl border border-transparent hover:border-blue-500 transition-colors duration-300 group mt-8 py-8 selection:bg-[#FF2D20] selection:text-white bg-gradient-to-b from-slate-400 to-slate-100 dark:from-slate-400 dark:text-white/50 overflow-hidden shadow-sm sm:rounded-lg">
                <div className="flex flex-col gap-4 justify-between sm:flex-row sm:items-end px-6">
                    <div className="flex flex-col gap-2 max-w-xl">
                        <h2 className="font-bold text-3xl text-white dark:text-black leading-tight pb-2 capitalize">
                            {location ?? "Passports"}
                        </h2>
                        <p className="text-sm text-white/80 dark:text-black/70">
                            Results fetched directly from the API with server-side pagination.
                        </p>
                    </div>
                    <div className="flex items-center gap-2 text-sm text-white/80 dark:text-black/70">
                        <label htmlFor="perPage">Rows per page</label>
                        <select
                            id="perPage"
                            className="rounded-md border border-gray-300 px-3 py-1 text-black"
                            value={perPage}
                            onChange={handlePerPageChange}
                        >
                            {[25, 50, 100].map((size) => (
                                <option key={size} value={size}>
                                    {size}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>
                <div className="overflow-x-auto">
                    {error && (
                        <div className="mx-6 mb-4 rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-700">
                            {error}
                        </div>
                    )}
                    <table className="min-w-full divide-y divide-gray-200 bg-white dark:bg-gray-300 text-sm">
                        <thead className="ltr:text-left rtl:text-right">
                            <tr className="font-semibold">
                                <th className="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    ID
                                </th>
                                <th className="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    First Name
                                </th>
                                <th className="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    Middle Name
                                </th>
                                <th className="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    Last Name
                                </th>
                                <th className="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    Date
                                </th>
                                <th className="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                    Request Number
                                </th>
                                <th className="px-4 py-2">&nbsp;</th>
                            </tr>
                        </thead>
                        {loading ? (
                            <tbody>
                                <tr>
                                    <td colSpan={7} className="px-4 py-8 text-center text-gray-500">
                                        Loading results...
                                    </td>
                                </tr>
                            </tbody>
                        ) : passportRows.length > 0 ? (
                            passportRows.map((passport) => (
                                <tbody
                                    className="divide-y divide-gray-200 pl-4 transition-opacity duration-500 ease-out"
                                    key={passport.id}
                                >
                                    <tr className="hover:bg-gray-100 cursor-pointer pl-4">
                                        <td className="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                            #{passport.id}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                            {passport.first_name ?? passport.firstName}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-2 text-gray-700">
                                            {passport.middle_name ?? passport.middleName}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-2 text-gray-700">
                                            {passport.last_name ?? passport.lastName}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-2 text-gray-700">
                                            {formatDate(passport.date_of_publish ?? passport.dateOfPublish)}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-2 text-gray-700">
                                            {passport.request_number ?? passport.requestNumber}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-2">
                                            <Link
                                                href={route("passport.showDetail", {
                                                    id: passport.id,
                                                })}
                                                className="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white transition ease-in-out delay-100 hover:-translate-y-1 hover:scale-110 hover:bg-[#FF2D20] duration-200"
                                            >
                                                Detail
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            ))
                        ) : (
                            <tbody className="text-center text-gray-500 dark:text-gray-400">
                                <tr>
                                    <td className="whitespace-nowrap px-4 py-2 text-center">
                                        No data found
                                    </td>
                                </tr>
                            </tbody>
                        )}
                    </table>
                    <div className="p-4 m-4">
                        <div className="flex flex-col items-center gap-4 text-sm text-gray-700 dark:text-gray-200 sm:flex-row sm:justify-between">
                            <div>
                                Page {paginationMeta.current_page ?? page} of {paginationMeta.last_page ?? 1}
                            </div>
                            <div className="flex items-center gap-2">
                                <button
                                    type="button"
                                    onClick={() => handlePageChange(Math.max(1, (paginationMeta.current_page ?? page) - 1))}
                                    disabled={!paginationLinks.prev && (paginationMeta.current_page ?? page) <= 1}
                                    className="rounded-md border border-gray-300 px-3 py-1 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Previous
                                </button>
                                <button
                                    type="button"
                                    onClick={() => handlePageChange((paginationMeta.current_page ?? page) + 1)}
                                    disabled={!paginationLinks.next && !(paginationMeta.has_more ?? false)}
                                    className="rounded-md border border-gray-300 px-3 py-1 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </AuthGuestLayout>
    );
}

export default LocationTableView;
