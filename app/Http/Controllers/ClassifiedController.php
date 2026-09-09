<?php

namespace App\Http\Controllers;

use App\Models\Classified;
use App\Models\ClassifiedReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClassifiedController extends Controller
{
    /**
     * Listing feed with optional category / campus / keyword filters.
     * Shows posts from every campus so students can connect across schools.
     */
    public function index(Request $request)
    {
        $query = Classified::with(['user', 'replies.user'])
            ->withCount('replies')
            ->latest();

        $category = $request->query('category');
        if ($category && array_key_exists($category, Classified::CATEGORIES)) {
            $query->where('category', $category);
        }

        $campus = $request->query('campus');
        if ($campus && in_array($campus, config('campuses.list'), true)) {
            $query->where('campus', $campus);
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        $classifieds = $query->paginate(15)->withQueryString();

        return view('pages.classifieds', [
            'classifieds' => $classifieds,
            'categories'  => Classified::CATEGORIES,
            'campuses'    => config('campuses.list'),
            'filters'     => compact('category', 'campus', 'search'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(Classified::CATEGORIES))],
            'title'    => ['required', 'string', 'max:120'],
            'body'     => ['required', 'string', 'max:4000'],
            'price'    => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ]);

        Auth::user()->classifieds()->create([
            'category' => $validated['category'],
            'title'    => $validated['title'],
            'body'     => $validated['body'],
            'price'    => $validated['price'] ?? null,
            'campus'   => Auth::user()->campus,
        ]);

        return redirect()->route('pages.classifieds')
                         ->with('success', 'Your post is live.');
    }

    public function update(Request $request, Classified $classified)
    {
        $this->authorizeOwner($classified);

        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(Classified::CATEGORIES))],
            'title'    => ['required', 'string', 'max:120'],
            'body'     => ['required', 'string', 'max:4000'],
            'price'    => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ]);

        $classified->update([
            'category' => $validated['category'],
            'title'    => $validated['title'],
            'body'     => $validated['body'],
            'price'    => $validated['price'] ?? null,
        ]);

        return redirect()->route('pages.classifieds')
                         ->with('success', 'Post updated.');
    }

    public function destroy(Classified $classified)
    {
        $this->authorizeOwner($classified);

        $classified->delete();

        return redirect()->route('pages.classifieds')
                         ->with('success', 'Post deleted.');
    }

    public function storeReply(Request $request, Classified $classified)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $classified->replies()->create([
            'user_id' => Auth::id(),
            'body'    => $validated['body'],
        ]);

        return redirect()->route('pages.classifieds')
                         ->with('success', 'Reply posted.');
    }

    public function destroyReply(ClassifiedReply $reply)
    {
        abort_unless($reply->user_id === Auth::id(), 403);

        $reply->delete();

        return redirect()->route('pages.classifieds')
                         ->with('success', 'Reply removed.');
    }

    /** Only the author may edit or delete their own listing. */
    private function authorizeOwner(Classified $classified): void
    {
        abort_unless($classified->user_id === Auth::id(), 403);
    }
}
