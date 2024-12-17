<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Str;

class BlogController extends Controller
{

    public function index()
    {
        $blogs = Blog::with('user')
            ->latest('published_at')
            ->paginate(10);
        Log::info("in BlogController index method");
        Log::info($blogs);
        return Inertia::render('Blog/Index', [
            'blogs' => $blogs
        ]);
    }

    public function create()
    {
        return Inertia::render('Blog/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'excerpt' => 'nullable',
            'featured_image' => 'nullable|image|max:2048'
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        if (Blog::where('slug', $validated['slug'])->exists()) {
            return back()->withErrors(['slug' => 'Slug already exists.']);
        }

        $validated['user_id'] = Auth::user()->id;
        $validated['published_at'] = now();

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('blog-images', 'public');
        }

        Blog::create($validated);

        return redirect()->route('blogs.index')->with('success', 'Blog post created successfully!');
    }

    public function show(Blog $blog)
    {
        return Inertia::render('Blog/Show', [
            'blog' => $blog->load('user')
        ]);
    }

    public function edit(Blog $blog)
    {
        return Inertia::render('Blog/Edit', [
            'blog' => $blog
        ]);
    }

    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'excerpt' => 'nullable',
            'featured_image' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('blog-images', 'public');
        }

        $blog->update($validated);

        return redirect()->route('blogs.show', $blog)->with('success', 'Blog post updated successfully!');
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();
        return redirect()->route('blogs.index')->with('success', 'Blog post deleted successfully!');
    }
}
