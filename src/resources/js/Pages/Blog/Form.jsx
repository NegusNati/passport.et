import React from "react";
import { useForm } from "@inertiajs/react";
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";
import InputError from "@/Components/InputError";
import TextInput from "@/Components/TextInput";

export default function Form({ blog = null }) {
    const { data, setData, post, put, processing, errors } = useForm({
        title: blog?.title ?? "",
        content: blog?.content ?? "",
        excerpt: blog?.excerpt ?? "",
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
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <label className="block text-sm font-medium text-white">
                                Blog Title
                            </label>
                            <TextInput
                                type="text"
                                value={data.title}
                                onChange={(e) =>
                                    setData("title", e.target.value)
                                }
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-blue-500"
                            />
                            {errors.title && (
                                <div className="text-red-500 text-sm mt-1">
                                    {errors.title}
                                </div>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-black">
                                Content
                            </label>
                            <textarea
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-blue-500"
                                rows="8"
                                value={data.content}
                                onChange={(e) =>
                                    setData("content", e.target.value)
                                }
                                required
                            />
                            <InputError
                                message={errors.content}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-black">
                                Excerpt
                            </label>
                            <textarea
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-blue-500"
                                rows="3"
                                value={data.excerpt}
                                onChange={(e) =>
                                    setData("excerpt", e.target.value)
                                }
                            />
                            <InputError
                                message={errors.excerpt}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-black">
                                Featured Image
                            </label>
                            <input
                                type="file"
                                onChange={(e) =>
                                    setData("featured_image", e.target.files[0])
                                }
                                className="mt-1 block w-full text-sm text-blue-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-indigo-50 file:text-indigo-700
                                        hover:file:bg-indigo-100"
                            />
                        </div>

                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                            >
                                {blog ? "Update Post" : "Create Post"}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthGuestLayout>
    );
}
