import React from "react";
import { useForm } from "@inertiajs/react";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";
import InputError from "@/Components/InputError";
import TextInput from "@/Components/TextInput";

export default function Form({ blog = null }) {
    const { data, setData } = useForm({
        title: blog?.title ?? "",
        content: blog?.content ?? "",
        excerpt: blog?.excerpt ?? "",
        meta_description: blog?.meta_description ?? "",
        meta_keywords: blog?.meta_keywords ?? "",
        featured_image: null,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (blog) {
            put(route("blogs.update", blog.id));
        } else {
            post(route("blogs.store"));
        }
    };

    return (
        <AuthGuestLayout title={blog ? "Edit Blog Post" : "Create Blog Post"}>
            <div className="py-6 sm:py-12 px-4 sm:px-0">
                <div className="max-w-3xl mx-auto">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="bg-white p-6 rounded-lg shadow-sm">
                            <div className="space-y-6">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Blog Title
                                    </label>
                                    <TextInput
                                        type="text"
                                        value={data.title}
                                        onChange={(e) =>
                                            setData("title", e.target.value)
                                        }
                                        className="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Enter blog title"
                                    />
                                    <InputError
                                        message={errors.title}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Content
                                    </label>
                                    <textarea
                                        className="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 min-h-[200px]"
                                        value={data.content}
                                        onChange={(e) =>
                                            setData("content", e.target.value)
                                        }
                                        placeholder="Write your blog content here..."
                                        required
                                    />
                                    <InputError
                                        message={errors.content}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Excerpt
                                    </label>
                                    <textarea
                                        className="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        rows="3"
                                        value={data.excerpt}
                                        onChange={(e) =>
                                            setData("excerpt", e.target.value)
                                        }
                                        placeholder="Brief summary of your post"
                                    />
                                    <InputError
                                        message={errors.excerpt}
                                        className="mt-2"
                                    />
                                </div>
                                <div>
                                    <label>Meta Description (SEO)</label>
                                    <textarea
                                        value={data.meta_description}
                                        onChange={(e) =>
                                            setData(
                                                "meta_description",
                                                e.target.value
                                            )
                                        }
                                        maxLength={160}
                                        className="w-full rounded-md"
                                        placeholder="Brief description for search engines"
                                    />
                                </div>

                                <div>
                                    <label>Meta Keywords (SEO)</label>
                                    <textarea
                                        value={data.meta_keywords}
                                        onChange={(e) =>
                                            setData(
                                                "meta_keywords ",
                                                e.target.value
                                            )
                                        }
                                        maxLength={160}
                                        className="w-full rounded-md"
                                        placeholder="Key words for search engines"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Featured Image
                                    </label>
                                    <input
                                        type="file"
                                        onChange={(e) =>
                                            setData(
                                                "featured_image",
                                                e.target.files[0]
                                            )
                                        }
                                        className="w-full text-sm text-gray-500
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-md file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-blue-50 file:text-blue-700
                                            hover:file:bg-blue-100
                                            cursor-pointer"
                                    />
                                    <InputError
                                        message={errors.featured_image}
                                        className="mt-2"
                                    />
                                </div>
                            </div>

                            <div className="mt-8">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {processing
                                        ? "Processing..."
                                        : blog
                                        ? "Update Post"
                                        : "Create Post"}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </AuthGuestLayout>
    );
}
