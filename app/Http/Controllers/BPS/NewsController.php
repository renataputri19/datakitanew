<?php

namespace App\Http\Controllers\BPS;

use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsController extends Controller
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
        $query = News::query();

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('excerpt', 'like', "%{$searchTerm}%");
        }

        // Handle category filter
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        $news = $query->latest('date')->paginate(10);

        // Append query parameters to pagination links
        $news->appends($request->query());

        return view('bps.news.index', compact('news'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bps.news.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'date' => 'required|date',
            'excerpt' => 'required|string|max:500',
            'source_url' => 'required|url',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'has_video' => 'boolean',
            'video_url' => 'nullable|url|required_if:has_video,1',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Ensure the news directory exists
            StorageHelper::ensureDirectoryExists('news');

            $thumbnailPath = $request->file('thumbnail')->store('news', 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        // Set has_video value
        $validated['has_video'] = $request->has('has_video');

        // Create news
        News::create($validated);

        return redirect()->route('bps.news.index')
            ->with('success', 'Berita berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news)
    {
        return view('bps.news.edit', compact('news'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'date' => 'required|date',
            'excerpt' => 'required|string|max:500',
            'source_url' => 'required|url',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'has_video' => 'boolean',
            'video_url' => 'nullable|url|required_if:has_video,1',
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($news->thumbnail && Storage::disk('public')->exists($news->thumbnail)) {
                Storage::disk('public')->delete($news->thumbnail);
            }

            // Ensure the news directory exists
            StorageHelper::ensureDirectoryExists('news');

            $thumbnailPath = $request->file('thumbnail')->store('news', 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        // Set has_video value
        $validated['has_video'] = $request->has('has_video');

        // Update news
        $news->update($validated);

        return redirect()->route('bps.news.index')
            ->with('success', 'Berita berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        // Delete thumbnail if exists
        if ($news->thumbnail && Storage::disk('public')->exists($news->thumbnail)) {
            Storage::disk('public')->delete($news->thumbnail);
        }

        // Delete news
        $news->delete();

        return redirect()->route('bps.news.index')
            ->with('success', 'Berita berhasil dihapus.');
    }
}
