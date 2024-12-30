import React from "react";
import { Link, useForm, Head } from "@inertiajs/react";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";
import SocialShare from "@/Components/SocialShare";

export default function Show({ blog, auth, isAdmin }) {
    const { delete: destroy } = useForm();

    const handleDelete = () => {
        if (confirm("Are you sure you want to delete this post?")) {
            destroy(route("blogs.destroy", blog?.id));
        }
    };

    if (!blog || Object.keys(blog).length === 0)
        return <div>blog Unavailable</div>;

    const metaImage = blog
        ? blog.og_image || blog.featured_image
        : "pass_welcome.png";

    let shareText = "";
    let shareUrl = "";

    if (blog) {
        shareUrl = `https://www.passport.et/blogs/${blog.id}`;
        shareText = `
        📰 ${blog.title}
        
        ${blog.excerpt || blog.content.substring(0, 150)}...
        
        Read more at: ${shareUrl}
        `;
    }

    return (
        <AuthGuestLayout user={auth.user}>
            <Head>
                <title>{blog ? `${blog?.title}` : "Blog Post"}</title>

                <meta property="og:site_name" content="Passport.ET" />
                <meta
                    property="og:title"
                    content={`${blog?.title || "Blog Posts"} | Passport.ET`}
                />
                <meta
                    property="og:description"
                    content={
                        blog?.meta_description ||
                        "Latest News and information about Ethiopian Immigration,Ethiopian Visa,Ethiopian Passport,Ethiopian Embassy"
                    }
                />
                <meta property="og:type" content="website" />
                <meta property="og:locale" content="en" />
                <meta
                    property="og:url"
                    content={
                        blog?.id
                            ? `https://www.passport.et/blogs/${blog?.id}`
                            : "https://www.passport.et"
                    }
                />
                <meta property="og:image" content={metaImage} />

                <meta
                    name="description"
                    content={
                        blog?.meta_description ||
                        "Latest News and information about Ethiopian Immigration,Ethiopian Visa,Ethiopian Passport,Ethiopian Embassy"
                    }
                />
                <meta
                    name="keywords"
                    content={
                        blog?.meta_keywords ||
                        "News, Ethiopian Immigration, Ethiopian Passport, Ethiopian Visa, Ethiopian Embassy, Blog"
                    }
                />
                <link
                    rel="canonical"
                    href={`${window.location.origin}/blogs/${blog?.id}`}
                />
            </Head>

            <div className="py-6 sm:py-12 px-4 sm:px-0">
                <div className="max-w-4xl mx-auto">
                    {blog?.featured_image && (
                        <div className="relative h-48 sm:h-64 md:h-96 mb-6">
                            <img
                                src={`/storage/${blog.featured_image}`}
                                alt={blog?.title || "passport.et news"}
                                className="w-full h-full object-cover rounded-lg"
                            />
                        </div>
                    )}

                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <h1 className="text-3xl sm:text-4xl text-gray-900 dark:text-white capitalize font-extrabold">
                            {blog?.title}
                        </h1>

                        <div className="flex gap-2 w-full sm:w-auto">
                            {isAdmin && (
                                <>
                                    <Link
                                        href={route("blogs.edit", blog?.id)}
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
                                </>
                            )}
                        </div>
                    </div>

                    <div className="flex flex-col sm:flex-row items-start sm:items-center text-gray-600 dark:text-gray-300 mb-8 gap-2 sm:gap-4 justify-between w-full">
                        <div>
                            {blog?.user && (
                                <span>
                                    By{" "}
                                    <span className="capitalize font-bold">
                                        {blog.user.first_name
                                            ? `${blog.user.first_name}`
                                            : "Admin"}
                                    </span>
                                </span>
                            )}
                            <span className="hidden sm:inline">{" "}•{" "}</span>
                            <span>
                                {blog?.published_at &&
                                    new Date(
                                        blog.published_at
                                    ).toLocaleDateString()}
                            </span>
                        </div>
                        {!isAdmin && (
                            <div className="relative">
                                <SocialShare
                                    shareText={shareText}
                                    shareUrl={shareUrl}
                                />
                            </div>
                        )}
                    </div>

                    {blog?.excerpt && (
                        <div className="text-base sm:text-lg text-gray-600 dark:text-gray-300 mb-8 italic font-light">
                            {blog.excerpt}
                        </div>
                    )}

                    {/* <div className="prose prose-sm sm:prose-lg max-w-none">
                        {blog?.content}
                    </div> */}
                    <div
                        className="prose prose-sm sm:prose-lg max-w-none dark:prose-invert"
                        dangerouslySetInnerHTML={{ __html: blog?.content }}
                    />

                    <div className="mt-8 sm:mt-12 border-t pt-6 sm:pt-8">
                        <Link
                            href={route("blogs.index")}
                            className="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition"
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
