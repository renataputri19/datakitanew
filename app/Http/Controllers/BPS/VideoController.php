<?php

namespace App\Http\Controllers\BPS;

use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'is_bps']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Video::query();

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('title', 'like', "%{$searchTerm}%");
        }

        $videos = $query->latest('date')->paginate(10);

        // Append query parameters to pagination links
        $videos->appends($request->query());

        return view('bps.videos.index', compact('videos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bps.videos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'url' => 'required|url',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Ensure the videos directory exists
            StorageHelper::ensureDirectoryExists('videos');

            $thumbnailPath = $request->file('thumbnail')->store('videos', 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        // Create video
        Video::create($validated);

        return redirect()->route('bps.videos.index')
            ->with('success', 'Video berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Video $video)
    {
        return view('bps.videos.edit', compact('video'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Video $video)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'url' => 'required|url',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($video->thumbnail && Storage::disk('public')->exists($video->thumbnail)) {
                Storage::disk('public')->delete($video->thumbnail);
            }

            // Ensure the videos directory exists
            StorageHelper::ensureDirectoryExists('videos');

            $thumbnailPath = $request->file('thumbnail')->store('videos', 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        // Update video
        $video->update($validated);

        return redirect()->route('bps.videos.index')
            ->with('success', 'Video berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Video $video)
    {
        // Delete thumbnail if exists
        if ($video->thumbnail && Storage::disk('public')->exists($video->thumbnail)) {
            Storage::disk('public')->delete($video->thumbnail);
        }

        // Delete video
        $video->delete();

        return redirect()->route('bps.videos.index')
            ->with('success', 'Video berhasil dihapus.');
    }
}
