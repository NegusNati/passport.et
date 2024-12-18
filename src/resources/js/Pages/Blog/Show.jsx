import React from "react";
import { Link, useForm, Head } from "@inertiajs/react";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";

export default function Show({ blog }) {
    const { delete: destroy } = useForm();

    const handleDelete = () => {
        if (confirm("Are you sure you want to delete this post?")) {
            destroy(route("blogs.destroy", blog.id));
        }
    };

    return (
        <AuthGuestLayout title={blog.title}>
            <Head>
                <title>{blog.title} | Your Site Name</title>
                <meta name="description" content={blog.meta_description} />
                <meta name="keywords" content={blog.meta_keywords} />
                <meta property="og:title" content={blog.title} />
                <meta property="og:description" content={blog.excerpt} />
                <meta
                    property="og:image"
                    content={`${window.location.origin}/storage/${blog.featured_image}`}
                />

                <script type="application/ld+json">
                    {JSON.stringify({
                        "@context": "https://schema.org",
                        "@type": "BlogPosting",
                        headline: blog.title,
                        image: `${window.location.origin}/storage/${blog.featured_image}`,
                        datePublished: blog.published_at,
                        dateModified: blog.updated_at,
                        author: {
                            "@type": "Person",
                            name: blog.user.name,
                        },
                    })}
                </script>
            </Head>
            <div className="py-6 sm:py-12 px-4 sm:px-0">
                <div className="max-w-4xl mx-auto">
                    {blog.featured_image && (
                        <div className="relative h-48 sm:h-64 md:h-96 mb-6">
                            <img
                                src={`/storage/${blog.featured_image}`}
                                alt={blog.title}
                                className="w-full h-full object-cover rounded-lg"
                            />
                        </div>
                    )}

                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <h1 className="text-3xl sm:text-4xl font-bold text-gray-900">
                            {blog.title}
                        </h1>

                        <div className="flex gap-2 w-full sm:w-auto">
                            <Link
                                href={route("blogs.edit", blog.id)}
                                className="flex-1 sm:flex-none text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                            >
                                Edit
                            </Link>
                            <button
                                onClick={handleDelete}
                                className="flex-1 sm:flex-none px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <div className="flex flex-col sm:flex-row items-start sm:items-center text-gray-600 mb-8 gap-2 sm:gap-4">
                        <span>By {blog.user.name}</span>
                        <span className="hidden sm:inline">•</span>
                        <span>
                            {new Date(blog.published_at).toLocaleDateString()}
                        </span>
                    </div>

                    {blog.excerpt && (
                        <div className="text-base sm:text-lg text-gray-600 mb-8 italic">
                            {blog.excerpt}
                        </div>
                    )}

                    <div className="prose prose-sm sm:prose-lg max-w-none">
                        {blog.content}
                    </div>

                    <div className="mt-8 sm:mt-12 border-t pt-6 sm:pt-8">
                        <Link
                            href={route("blogs.index")}
                            className="inline-flex items-center text-blue-600 hover:text-blue-800 transition"
                        >
                            <span>←</span>
                            <span className="ml-2">Back to all posts</span>
                        </Link>
                    </div>
                </div>
            </div>
        </AuthGuestLayout>
    );
}
