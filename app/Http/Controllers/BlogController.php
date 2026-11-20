<?php

namespace App\Http\Controllers;

use App\Domain\Content\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        return view('storefront.blog.index', [
            'posts' => BlogPost::query()
                ->published()
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%'.$search.'%')
                        ->orWhere('excerpt', 'like', '%'.$search.'%');
                }))
                ->latest('published_at')
                ->paginate(10)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function show(BlogPost $blogPost)
    {
        abort_unless($blogPost->is_published && $blogPost->published_at, 404);

        $relatedPosts = BlogPost::query()
            ->published()
            ->where('id', '!=', $blogPost->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('storefront.blog.show', compact('blogPost', 'relatedPosts'));
    }
}
