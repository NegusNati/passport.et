import React from "react";
import { Link, Head } from "@inertiajs/react";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";

export default function Index({ blogs }) {
    return (
        <AuthGuestLayout title="Blog Posts">
            <Head>
                <title>Blog Posts | Passport.ET</title>
                <meta
                    name="description"
                    content="Latest News and information about Ethiopian Immigration,Ethiopian Visa,Ethiopian Passport,Ethiopian Embassy"
                />
                <meta
                    name="keywords"
                    content="News, Ethiopian Immigration, Ethiopian Passport, Ethiopian Visa, Ethiopian Embassy, Blog"
                />
                <link
                    rel="canonical"
                    href={`${window.location.origin}/blogs`}
                />
            </Head>
            <div className="py-6 sm:py-12 px-4 sm:px-0">
                <div className="max-w-7xl mx-auto">
                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <h1 className="text-2xl sm:text-3xl font-semibold text-gray-900">
                            Blog Posts
                        </h1>
                        <Link
                            href={route("blogs.create")}
                            className="w-full sm:w-auto text-center px-4 py-2 bg-blue-600 text-blue-400 rounded-md hover:bg-blue-700 transition"
                        >
                            Create New Post
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 gap-4 sm:gap-6">
                        {blogs.data.map((blog) => (
                            <div
                                key={blog.id}
                                className="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition"
                            >
                                <div className="flex flex-col sm:flex-row">
                                    {blog.featured_image && (
                                        <div className="sm:w-1/3">
                                            <img
                                                src={`/storage/${blog.featured_image}`}
                                                alt={blog.title}
                                                className="w-full h-48 sm:h-full object-cover"
                                                onError={(e) => {
                                                    e.target.src =
                                                        "./pass_welcome.png";
                                                }}
                                            />
                                        </div>
                                    )}
                                    <div className="p-4 sm:p-6 flex-1">
                                        <h2 className="text-xl font-semibold mb-2">
                                            <Link
                                                href={route(
                                                    "blogs.show",
                                                    blog.id
                                                )}
                                                className="hover:text-blue-600 transition"
                                            >
                                                {blog.title}
                                            </Link>
                                        </h2>
                                        <p className="text-gray-600 mb-4 line-clamp-3">
                                            {blog.excerpt}
                                        </p>
                                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center text-sm text-gray-500 gap-2">
                                            <span>
                                                By{" "}
                                                {blog.user.first_name
                                                    ? `${blog.user.first_name}`
                                                    : "Admin"}
                                            </span>
                                            <span>
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
