import Authenticated from "@/Layouts/AuthenticatedLayout";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import Pagination from "@/Components/Pagination";

function Show({ auth, passports, search }) {
    const { requestNumber, firstName, middleName, lastName } = search;

    const formatDate = (dateString) => {
        const options = { year: "numeric", month: "long", day: "numeric" };
        const date = new Date(dateString);
        return date.toLocaleDateString(undefined, options);
    };

    return (
        <AuthGuestLayout
            user={auth.user}
            header={
                <h1 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight py-4">
                    {/* Display search parameters */}
                    {requestNumber && (
                        <div>Request Number: {requestNumber}</div>
                    )}
                    {firstName && <div>First Name: {firstName}</div>}
                    {middleName && <div>Middle Name: {middleName}</div>}
                    {lastName && <div>Last Name: {lastName}</div>}
                    {!(
                        requestNumber ||
                        firstName ||
                        middleName ||
                        lastName
                    ) && (
                        <div className="text-red-500 text-sm">
                            Please enter at least one search parameter to
                            display the exact results.
                            <a
                                href={route("passport")}
                                className="text-blue-500 text-sm font-bold"
                            >
                                {" "}
                                Go Back
                            </a>
                        </div>
                    )}
                </h1>
            }
        >
            <Head title="Results" />

            <main className="mt-8 max-w-[990px] m-auto selection:bg-[#FF2D20] selection:text-white mb-20 pb-40 ">
                {passports.length > 0 ? (
                    passports.map((passport) => (
                        <Link
                            href={route("passport.showDetail", {
                                id: passport.id,
                            })}
                            key={passport.id}
                        >
                            <div
                                className=" bg-white/80 dark:bg-black/80 text-black dark:text-white my-2   p-4 rounded-2xl  border border-transparent  hover:border-blue-500 transition-colors duration-300 group shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] md:row-span-3 lg:p-10 lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20] "
                                key={passport.id}
                            >
                                <div className="px-4 w-full ">
                                    <div className=" space-y-2 pb-2  flex space-x-2 justify-around py-4">
                                        {/* <div className="flex items-center lg:w-[200px] ">
                                    <img src={`https://picsum.photos/seed/${Math.random(1,100)}/200`} width="50" className="rounded-xl w-10 h-10 object-cover " />
                                    </div> */}
                                        <div className="space-y-2 ">
                                            <p className="text-xs capitalize ">
                                                First Name :{" "}
                                                <strong>
                                                    {passport.firstName}
                                                </strong>
                                            </p>
                                            <p className="text-xs capitalize ">
                                                Middle Name :{" "}
                                                <strong>
                                                    {passport.middleName}
                                                </strong>
                                            </p>
                                            <p className="text-xs capitalize ">
                                                Last Name :{" "}
                                                <strong>
                                                    {passport.lastName}
                                                </strong>
                                            </p>
                                        </div>
                                        <div className=" ">
                                            <p className="text-xs capitalize ">
                                                Request Number :{" "}
                                                <strong>
                                                    {passport.requestNumber}
                                                </strong>
                                            </p>
                                            <p className="text-xs capitalize ">
                                                can riceive after:{" "}
                                                <strong className="text-blue-500 dark:text-blue-400 font-bold">
                                                    {" "}
                                                    {formatDate(
                                                        passport.dateOfPublish
                                                    )}
                                                </strong>
                                            </p>
                                            <p>
                                                Location :{" "}
                                                <strong>
                                                    {passport.location}
                                                </strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    ))
                ) : (
                    <div className=" bg-white/80 text-black my-2   p-4 rounded-2xl  border border-transparent  hover:border-blue-500  group shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] md:row-span-3 lg:p-10 lg:pb-10 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-red-500 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20] ">
                        <p className="text-center font-bold dark:text-white/70">
                            It Seems like your passport is not ready, come back
                            tommorow.
                        </p>
                    </div>
                )}
            </main>
        </AuthGuestLayout>
    );
}

export default Show;
