<?php

namespace App\Http\Controllers;

use App\Models\NewsPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsPost::with('author')->latest();

        $category = $request->query('category');
        if ($category && array_key_exists($category, NewsPost::CATEGORIES)) {
            $query->where('category', $category);
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        return view('pages.news', [
            'posts'      => $query->paginate(10)->withQueryString(),
            'categories' => NewsPost::CATEGORIES,
            'filters'    => ['category' => $category, 'search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:180'],
            'category' => ['required', Rule::in(array_keys(NewsPost::CATEGORIES))],
            'body'     => ['required', 'string', 'max:8000'],
        ]);

        NewsPost::create([
            'user_id'  => Auth::id(),
            'title'    => $validated['title'],
            'category' => $validated['category'],
            'body'     => $validated['body'],
            'campus'   => Auth::user()->campus,
        ]);

        return redirect()->route('pages.news')->with('success', 'News posted.');
    }

    public function show(NewsPost $news)
    {
        $news->load('author');

        return view('pages.news-article', [
            'post'    => $news,
            'isOwner' => $news->user_id === Auth::id(),
        ]);
    }

    public function update(Request $request, NewsPost $news)
    {
        abort_unless($news->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:180'],
            'category' => ['required', Rule::in(array_keys(NewsPost::CATEGORIES))],
            'body'     => ['required', 'string', 'max:8000'],
        ]);

        $news->update($validated);

        return redirect()->route('news.show', $news)->with('success', 'News updated.');
    }

    public function destroy(NewsPost $news)
    {
        abort_unless($news->user_id === Auth::id(), 403);

        $news->delete();

        return redirect()->route('pages.news')->with('success', 'News deleted.');
    }
}
