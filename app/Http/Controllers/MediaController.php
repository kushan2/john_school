<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public const VISIBILITIES = [
        'private' => 'Only me',
        'campus'  => 'My campus',
        'public'  => 'Everyone',
    ];

    public function index(Request $request)
    {
        $me = Auth::user();
        $filter = $request->query('filter', 'all');

        $query = Media::query()
            ->where('collection_name', 'files')
            ->with('model')
            ->latest();

        // Hard visibility gate — nothing the user isn't allowed to see leaks in.
        $query->where(function ($sub) use ($me) {
            $sub->where(fn ($o) => $o->where('model_type', $me->getMorphClass())->where('model_id', $me->id))
                ->orWhere('custom_properties->visibility', 'public')
                ->orWhere(fn ($c) => $c->where('custom_properties->visibility', 'campus')
                                       ->where('custom_properties->campus', $me->campus));
        });

        // Optional narrowing filters on top of the gate.
        if ($filter === 'mine') {
            $query->where('model_type', $me->getMorphClass())->where('model_id', $me->id);
        } elseif ($filter === 'public') {
            $query->where('custom_properties->visibility', 'public');
        } elseif ($filter === 'campus') {
            $query->where('custom_properties->visibility', 'campus')
                  ->where('custom_properties->campus', $me->campus);
        }

        return view('pages.media', [
            'files'        => $query->paginate(24)->withQueryString(),
            'visibilities' => self::VISIBILITIES,
            'filter'       => $filter,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file'       => ['required', 'file', 'max:10240', // 10 MB
                'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,csv,zip'],
            'visibility' => ['required', Rule::in(array_keys(self::VISIBILITIES))],
        ]);

        Auth::user()
            ->addMediaFromRequest('file')
            ->withCustomProperties([
                'visibility' => $validated['visibility'],
                'campus'     => Auth::user()->campus,
            ])
            ->toMediaCollection('files');

        return redirect()->route('pages.media')->with('success', 'File uploaded.');
    }

    /** Stream the file inline (used for image thumbnails and previews). */
    public function show(Media $media)
    {
        abort_unless($this->canView($media, Auth::user()), 403);

        return response()->file($media->getPath());
    }

    /** Force a download with the original filename. */
    public function download(Media $media)
    {
        abort_unless($this->canView($media, Auth::user()), 403);

        return response()->download($media->getPath(), $media->file_name);
    }

    public function destroy(Media $media)
    {
        abort_unless($this->owns($media, Auth::user()), 403);

        $media->delete();

        return redirect()->route('pages.media')->with('success', 'File deleted.');
    }

    private function owns(Media $media, User $user): bool
    {
        return $media->model_type === $user->getMorphClass()
            && (int) $media->model_id === $user->id;
    }

    private function canView(Media $media, User $user): bool
    {
        if ($this->owns($media, $user)) {
            return true;
        }

        return match ($media->getCustomProperty('visibility')) {
            'public' => true,
            'campus' => $media->getCustomProperty('campus') !== null
                     && $media->getCustomProperty('campus') === $user->campus,
            default  => false, // private
        };
    }
}
