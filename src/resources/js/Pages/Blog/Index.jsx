import React from "react";
import { Link, Head } from "@inertiajs/react";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";

export default function Index({ blogs, auth, isAdmin }) {
    console.log("in index", blogs.data);
    return (
        <AuthGuestLayout user={auth.user}>
            <Head>
                <title>Latest Articles</title>

                <meta property="og:type" content="website" />
                <meta
                    property="og:url"
                    content={`${window.location.origin}/blogs`}
                />
                <meta
                    property="og:title"
                    content="Latest Articles | Ethiopian Immigration & Passport News - Passport.ET Blog"
                />
                <meta
                    property="og:description"
                    content="Stay informed with daily news and articles about Ethiopian Immigration, Visa, Passport, Embassy services, and Ethiopian Airlines. Your source for up-to-date information."
                />
                <meta property="og:site_name" content="Passport.ET" />
                <meta property="og:locale" content="en_US" />
                <meta
                    property="og:image"
                    content={asset("PASSPORT1-webp.webp")}
                />
                <meta
                    property="og:image:alt"
                    content="Passport.ET Blog - Latest Articles on Ethiopian Immigration and Passport Services"
                />

                {/* now for X */}

                <meta name="twitter:card" content="summary_large_image" />
                <meta
                    name="twitter:url"
                    content={`${window.location.origin}/blogs`}
                />
                <meta
                    name="twitter:title"
                    content="Latest Articles | Ethiopian Immigration & Passport News - Passport.ET Blog"
                />
                <meta
                    name="twitter:description"
                    content="Stay informed with daily news and articles about Ethiopian Immigration, Visa, Passport, Embassy services, and Ethiopian Airlines. Your source for up-to-date information."
                />
                <meta
                    name="twitter:image"
                    content={asset("PASSPORT1-webp.webp")}
                />
            </Head>
            <div className="py-6 sm:py-12 px-4 sm:px-0 ">
                <div className="max-w-7xl mx-auto">
                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <h1 className="mb-4 text-2xl font-extrabold text-gray-900 dark:text-white md:text-4xl lg:text-5xl">
                            Latest{" "}
                            <span className="capitalize text-transparent bg-clip-text bg-gradient-to-r to-emerald-600 from-sky-400">
                                Articles
                            </span>
                        </h1>
                        {isAdmin && (
                            <Link
                                href={route("blogs.create")}
                                className="w-full sm:w-auto text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                            >
                                Create New Article
                            </Link>
                        )}
                    </div>

                    <div className="grid grid-cols-1 gap-4 sm:gap-6 min-h-screen justify-between content-start">
                        {blogs.data?.length === 0 && (
                            <div className="text-center text-gray-600 dark:text-gray-400">
                                No articles found for now.
                            </div>
                        )}
                        {blogs.data?.map((blog) => (
                            <div
                                key={blog.id}
                                className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hover:shadow-md transition h-full"
                            >
                                <div className="flex flex-col sm:flex-row">
                                    {blog.featured_image && (
                                        <div className="sm:w-1/3">
                                            <img
                                                loading="lazy"
                                                width="800"
                                                height="400"
                                                src={`/storage/${blog.featured_image}`}
                                                alt={blog.title}
                                                className="w-full h-48 sm:h-full object-cover"
                                                onError={(e) => {
                                                    e.target.src =
                                                        "./PASSPORT1-webp.webp";
                                                }}
                                            />
                                        </div>
                                    )}
                                    <div className="p-4 sm:p-6 flex-1 flex flex-col justify-between">
                                        <div>
                                            <h2 className="text-xl font-semibold mb-2">
                                                <Link
                                                    href={route(
                                                        "blogs.show",
                                                        blog.id
                                                    )}
                                                    className="text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition capitalize font-extrabold"
                                                >
                                                    {blog.title}
                                                </Link>
                                            </h2>
                                            <p
                                                className="text-gray-600 dark:text-gray-300 mb-4 line-clamp-3"
                                                dangerouslySetInnerHTML={{
                                                    __html:
                                                        blog.excerpt ||
                                                        blog.content.substring(
                                                            0,
                                                            150
                                                        ) + "...",
                                                }}
                                            />
                                        </div>
                                        <div className="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
                                            <span>
                                                By{" "}
                                                <span className="capitalize font-bold">
                                                    {blog.user.first_name
                                                        ? `${blog.user.first_name}`
                                                        : "Admin"}
                                                </span>
                                            </span>
                                            <span className="font-bold">
                                                {new Date(
                                                    blog.published_at
                                                ).toLocaleDateString()}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AuthGuestLayout>
    );
}
