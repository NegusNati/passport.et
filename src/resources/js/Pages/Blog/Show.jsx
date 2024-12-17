import React from 'react';
import { Link, useForm } from '@inertiajs/react';
import AuthGuestLayout from "@/Layouts/AuthGuestLayout";




export default function Show({ blog }) {
    const { delete: destroy } = useForm();

    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this post?')) {
            destroy(route('blogs.destroy', blog.id));
        }
    };

    return (
        <AuthGuestLayout title={blog.title}>
            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    {blog.featured_image && (
                        <img
                            src={`/storage/${blog.featured_image}`}
                            alt={blog.title}
                            className="w-full h-64 object-cover rounded-lg mb-8"
                        />
                    )}

                    <div className="flex justify-between items-center mb-8">
                        <h1 className="text-4xl font-bold text-gray-900">{blog.title}</h1>

                        <div className="flex gap-4">
                            <Link
                                href={route('blogs.edit', blog.id)}
                                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            >
                                Edit
                            </Link>
                            <button
                                onClick={handleDelete}
                                className="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <div className="flex items-center text-gray-600 mb-8">
                        <span>By {blog.user.name}</span>
                        <span className="mx-2">•</span>
                        <span>{new Date(blog.published_at).toLocaleDateString()}</span>
                    </div>

                    {blog.excerpt && (
                        <div className="text-lg text-gray-600 mb-8 italic">
                            {blog.excerpt}
                        </div>
                    )}

                    <div className="prose prose-lg max-w-none">
                        {blog.content}
                    </div>

                    <div className="mt-12 border-t pt-8">
                        <Link
                            href={route('blogs.index')}
                            className="text-blue-600 hover:text-blue-800"
                        >
                            ← Back to all posts
                        </Link>
                    </div>
                </div>
            </div>
        </AuthGuestLayout>
    );
}
