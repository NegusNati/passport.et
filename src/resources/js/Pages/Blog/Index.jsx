import React from "react";
import { Link } from "@inertiajs/react";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";

export default function Index({ blogs }) {
    return (
        <AuthGuestLayout title="Blog Posts">
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center mb-6">
                        <h1 className="text-3xl font-semibold text-gray-900">
                            Blog Posts
                        </h1>
                        <Link
                            href={route("blogs.create")}
                            className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            Create New Post
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {blogs.data.map((blog) => (
                            <div
                                key={blog.id}
                                className="bg-white overflow-hidden shadow-sm rounded-lg"
                            >
                                {blog.featured_image && (
                                    <img
                                        src={`/storage/${blog.featured_image}`}
                                        alt={blog.title}
                                        className="w-full h-48 object-cover"
                                    />
                                )}
                                <div className="p-6">
                                    <h2 className="text-xl font-semibold mb-2">
                                        <Link
                                            href={route("blogs.show", blog.id)}
                                            className="hover:text-blue-600"
                                        >
                                            {blog.title}
                                        </Link>
                                    </h2>
                                    <p className="text-gray-600 mb-4">
                                        {blog.excerpt}
                                    </p>
                                    <div className="flex justify-between items-center text-sm text-gray-500">
                                        <span>By {blog.user.name}</span>
                                        <span>
                                            {new Date(
                                                blog.published_at
                                            ).toLocaleDateString()}
                                        </span>
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
