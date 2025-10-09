import AuthGuestLayout from "@/Layouts/AuthGuestLayout";
import { Head, Link } from "@inertiajs/react";
import formatDate from "@/helpers/formarDate";
import React, { useCallback, useEffect, useMemo, useState } from "react";
import { fetchLocations, fetchPassports } from "@/api/passports";

const sanitizeParams = (params) =>
    Object.entries(params).reduce((acc, [key, value]) => {
        if (value !== null && value !== undefined && value !== "") {
            acc[key] = value;
        }
        return acc;
    }, {});

function TableView({ auth }) {
    const [passports, setPassports] = useState({
        data: [],
        links: {},
        meta: {},
        filters: {},
    });
    const [filters, setFilters] = useState({
        request_number: "",
        location: "",
        published_after: "",
        published_before: "",
    });
    const [page, setPage] = useState(1);
    const [perPage, setPerPage] = useState(10);
    const [locations, setLocations] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const loadPassports = useCallback(
        async (overrides = {}) => {
            setLoading(true);
            setError(null);
            try {
                const params = sanitizeParams({
                    ...filters,
                    per_page: overrides.perPage ?? perPage,
                    page: overrides.page ?? page,
                });

                const response = await fetchPassports(params);
                setPassports({
                    data: response.data ?? [],
                    links: response.links ?? {},
                    meta: response.meta ?? {},
                    filters: response.filters ?? {},
                });
                if (response.meta?.current_page) {
                    setPage(response.meta.current_page);
                }
            } catch (err) {
                setError(
                    err?.response?.data?.message || err.message || "Unexpected error"
                );
            } finally {
                setLoading(false);
            }
        },
        [filters, page, perPage]
    );

    useEffect(() => {
        loadPassports();
    }, [loadPassports]);

    useEffect(() => {
        let mounted = true;
        fetchLocations()
            .then((response) => {
                if (!mounted) return;
                const items = Array.isArray(response?.data)
                    ? response.data
                    : response;
                setLocations(items ?? []);
            })
            .catch(() => {
                // ignore for now
            });

        return () => {
            mounted = false;
        };
    }, []);

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

    const handleFilterChange = (event) => {
        const { name, value } = event.target;
        setFilters((prev) => ({ ...prev, [name]: value }));
    };

    const applyFilters = (event) => {
        event.preventDefault();
        setPage(1);
        loadPassports({ page: 1 });
    };

    const clearFilters = () => {
        setFilters({
            request_number: "",
            location: "",
            published_after: "",
            published_before: "",
        });
        setPage(1);
        loadPassports({ page: 1 });
    };

    const paginationMeta = useMemo(() => passports.meta || {}, [passports.meta]);
    const paginationLinks = useMemo(() => passports.links || {}, [passports.links]);
    const passportRows = passports.data || [];

    return (
        <AuthGuestLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight py-4 capitalize">
                    daily updated Passports {formatDate(new Date())}
                </h2>
            }
        >
            <Head title="All Passports" />
            <main className="max-w-[990px] m-auto mb-20 bg-gray-200 rounded-2xl border border-transparent hover:border-blue-500 transition-colors duration-300 group mt-8 py-8 selection:bg-[#FF2D20] selection:text-white bg-gradient-to-b from-slate-400 to-slate-100 dark:from-slate-400 dark:text-white/50 overflow-hidden shadow-sm sm:rounded-lg">
                <div className="flex flex-col gap-4 justify-between sm:flex-row sm:items-end px-6">
                    <div className="flex flex-col gap-2 max-w-xl">
                        <h1 className="font-bold text-3xl text-white dark:text-black leading-tight pb-2 capitalize">
                            Latest Passport Arrivals
                        </h1>
                        <p className="text-sm text-white/80 dark:text-black/70">
                            Powered by the new API layer with server-side filtering, sorting, and pagination.
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
                            {[10, 25, 50, 100].map((size) => (
                                <option key={size} value={size}>
                                    {size}
                                </option>
                            ))}
                        </select>
                    </div>
                </div>
                <div className="px-6 py-4">
                    <form
                        onSubmit={applyFilters}
                        className="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6"
                    >
                        <div className="col-span-1 md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Request Number
                            </label>
                            <input
                                type="text"
                                name="request_number"
                                value={filters.request_number}
                                onChange={handleFilterChange}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="AA123456"
                            />
                        </div>
                        <div className="col-span-1 md:col-span-2">
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Location
                            </label>
                            <select
                                name="location"
                                value={filters.location}
                                onChange={handleFilterChange}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">All locations</option>
                                {locations.map((loc) => (
                                    <option key={loc} value={loc}>
                                        {loc}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Published After
                            </label>
                            <input
                                type="date"
                                name="published_after"
                                value={filters.published_after}
                                onChange={handleFilterChange}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Published Before
                            </label>
                            <input
                                type="date"
                                name="published_before"
                                value={filters.published_before}
                                onChange={handleFilterChange}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <div className="flex items-end gap-2">
                            <button
                                type="submit"
                                className="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                disabled={loading}
                            >
                                Apply
                            </button>
                            <button
                                type="button"
                                className="inline-flex items-center rounded-md border border-transparent px-4 py-2 text-sm font-semibold text-gray-700 hover:text-gray-900 focus:outline-none"
                                onClick={clearFilters}
                                disabled={loading}
                            >
                                Reset
                            </button>
                        </div>
                    </form>
                    {error && (
                        <div className="mb-4 rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-700">
                            {error}
                        </div>
                    )}
                </div>
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50 dark:bg-gray-500 dark:text-white">
                            <tr>
                                <th scope="col" className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    ID
                                </th>
                                <th scope="col" className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    First Name
                                </th>
                                <th scope="col" className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Middle Name
                                </th>
                                <th scope="col" className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Last Name
                                </th>
                                <th scope="col" className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Date
                                </th>
                                <th scope="col" className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Request Number
                                </th>
                                <th scope="col" className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Action
                                </th>
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
                                <tbody className="divide-y divide-gray-200 pl-4 transition-opacity duration-500 ease-out" key={passport.id}>
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

export default TableView;
