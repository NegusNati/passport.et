<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\User as User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Cache::remember('blog_index_page', 3600, function () {
            return Blog::with('user')
                ->latest('published_at')
                ->paginate(10);
        });

        return Inertia::render('Blog/Index', [
            'blogs' => $blogs,
            'isAdmin' => auth()->user()?->can('upload-files') ?? false
        ]);
    }

    public function create()
    {
        return Inertia::render('Blog/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable',
            'featured_image' => 'nullable|image|max:5120',
            'og_image' => 'nullable|image|max:5120',
            "meta_description" => "nullable",
            "meta_keywords" => "nullable"
        ]);
        Log::info("in BlogController store method");
        Log::info($request);
        $validated['meta_description'] = $request['meta_description'];
        $validated['meta_keywords'] = $request['meta_keywords'];



        $validated['slug'] = Str::slug($validated['title']);
        $slug = $validated['slug'];
        $count = 1;
        while (Blog::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $slug . '-' . $count++;
        }
        if (!Auth::check()) {
            return abort(403);
        }


        $validated['user_id'] = Auth::id();
        $validated['published_at'] = now();

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('blog-images/featured', 'public');
        }

        if ($request->hasFile('og_image')) {
            $validated['og_image'] = $request->file('og_image')->store('blog-images/og', 'public');
        }
        Log::info($validated);
        Blog::create($validated);

        return redirect()->route('blogs.index')->with('success', 'Blog post created successfully!');
    }

    public function show(Blog $blog)
    {
        return Inertia::render('Blog/Show', [
            'blog' => $blog->load('user'),
            'isAdmin' => auth()->user()?->can('upload-files') ?? false
        ]);
    }


    public function edit(Blog $blog)
    {
        $user = Auth::user();
        if ($user !== null && $user instanceof User) {
            if (!$user->can('upload-files')) {
                abort(403, 'Unauthorized action.');
            }
        }

        return Inertia::render('Blog/Form', [
            'blog' => $blog
        ]);
    }

    public function update(Request $request, Blog $blog)
    {
        Log::info('in update  ');
        Log::info('Request headers:', $request->headers->all());
        Log::info('Request headers:', $request->all());


        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable',
            'featured_image' => 'nullable|image|max:5120',
            'og_image' => 'nullable|image|max:5120',
            'meta_description' => 'nullable',
            'meta_keywords' => 'nullable'
        ]);

        $validated['meta_description'] = $request['meta_description'];
        $validated['meta_keywords'] = $request['meta_keywords'];


        if (!Auth::check()) {
            return abort(403);
        }

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('blog-images/featured', 'public');
        }
        if ($request->hasFile('og_image')) {
            $validated['og_image'] = $request->file('og_image')->store('blog-images/og', 'public');
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
