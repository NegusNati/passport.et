import Authenticated from "@/Layouts/AuthenticatedLayout";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import formatDate from "@/helpers/formarDate";
import React, { useState } from "react";
import Pagination from "@/Components/Pagination";

function TableView({ auth, passports }) {
    let { data, links } = passports;

    // const [filterBy, setFilterBy] = useState("");
    // const [filterValue, setFilterValue] = useState("");

    const [transitionClass, setTransitionClass] = useState("");

    // const handleFilter = () => {
    //     // Filter the data based on the selected filter criteria
    //     let filteredData = data;
    //     if (filterBy === "alphabet") {
    //         filteredData = data.filter((item) =>
    //             item.firstName
    //                 .toLowerCase()
    //                 .startsWith(filterValue.toLowerCase())
    //         );
    //     } else if (filterBy === "time") {
    //         filteredData = data.filter(
    //             (item) => new Date(item.createdAt) >= new Date(filterValue)
    //         );
    //     }

    //     data = filteredData;
    // };

    // Step 2: Toggle the transition class when the page changes
    // This assumes you have a function to handle page change
    const handlePageChange = (newPage) => {
        // Your logic to fetch new data based on the new page
        // After fetching new data, toggle the transition class
        setTransitionClass(
            newPage === passports.current_page ? "" : "opacity-0"
        );
        setTimeout(() => setTransitionClass(""), 500); // Reset after the transition completes
    };

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
            <main className="  max-w-[990px] m-auto  mb-20 bg-gray-200 rounded-2xl border border-transparent  hover:border-blue-500 transition-colors duration-300 group mt-8 py-8 selection:bg-[#FF2D20] selection:text-white bg-gradient-to-b from-slate-400 to-slate-100 dark:from-slate-400  dark:text-white/50 overflow-hidden shadow-sm sm:rounded-lg ">
                <div>
                    <div className="flex justify-center items-center">
                        <h2 className="font-bold text-3xl text-white dark:text-black leading-tight pb-4 capitalize   ">
                            Latest Passports
                        </h2>
                        {/* <div className="flex items-center">
                            <select
                                className="mr-4 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                value={filterBy}
                                onChange={(e) => setFilterBy(e.target.value)}
                            >
                                <option value="">Filter by</option>
                                <option value="alphabet">Alphabet</option>
                                <option value="time">Time</option>
                            </select>
                            {filterBy === "alphabet" && (
                                <input
                                    type="text"
                                    className="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Filter by name"
                                    value={filterValue}
                                    onChange={(e) =>
                                        setFilterValue(e.target.value)
                                    }
                                />
                            )}
                            {filterBy === "time" && (
                                <input
                                    type="date"
                                    className="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    value={filterValue}
                                    onChange={(e) =>
                                        setFilterValue(e.target.value)
                                    }
                                />
                            )}
                            <button
                                className="ml-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                onClick={handleFilter}
                            >
                                Filter
                            </button>
                        </div> */}
                    </div>
                </div>
                <div className="overflow-x-auto ">
                    <table className="min-w-full divide-y-2 divide-gray-200 bg-white dark:bg-gray-300 text-sm p-4">
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
                                <th className="px-4 py-2"> {"         "} </th>
                            </tr>
                        </thead>
                        {data.length > 0 ? (
                            data?.map((passport) => (
                                <tbody
                                    className={`divide-y divide-gray-200 pl-4 transition-opacity duration-500 ease-out ${transitionClass}`}
                                    key={passport.id}
                                >
                                    <tr
                                        key={passport.id}
                                        className="hover:bg-gray-100 cursor-pointer pl-4"
                                    >
                                        <td className="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                            #{passport.id}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-2 font-medium text-gray-900">
                                            {passport.firstName}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-2 text-gray-700">
                                            {passport.middleName}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-2 text-gray-700">
                                            {passport.lastName}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-2 text-gray-700">
                                            {formatDate(passport.dateOfPublish)}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-2 text-gray-700">
                                            {passport.requestNumber}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-2">
                                            <Link
                                                href={route(
                                                    "passport.showDetail",
                                                    {
                                                        id: passport.id,
                                                    }
                                                )}
                                                className="inline-block rounded bg-indigo-600 px-4 py-2 text-xs font-medium text-white  transition ease-in-out delay-100  hover:-translate-y-1 hover:scale-110 hover:bg-[#FF2D20] duration-200"
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
                                        {" "}
                                        No data found
                                    </td>
                                </tr>
                            </tbody>
                        )}
                    </table>
                    <div className="p-4 m-4">
                        <Pagination
                            passports={passports}
                            handlePageChange={handlePageChange}
                        />
                    </div>
                </div>
            </main>
        </AuthGuestLayout>
    );
}

export default TableView;
