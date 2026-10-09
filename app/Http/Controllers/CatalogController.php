<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Figure;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Figure::active()
            ->with('category');

        if ($request->has('category') && $request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%'.addcslashes($request->search, '%_\\').'%');
        }

        $figures = $query->orderByRaw('stock > 0 desc')
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $categories = Category::active()
            ->whereHas('figures', function ($query) {
                $query->active();
            })
            ->get();

        $featured = Figure::active()
            ->featured()
            ->with('category')
            ->limit(6)
            ->get();

        return view('catalog.index', compact('figures', 'categories', 'featured'));
    }

    public function show(string $slug)
    {
        $figure = Figure::where('slug', $slug)
            ->active()
            ->with('category')
            ->firstOrFail();

        $relatedFigures = Figure::active()
            ->where('category_id', $figure->category_id)
            ->where('id', '!=', $figure->id)
            ->limit(4)
            ->get();

        return view('catalog.show', compact('figure', 'relatedFigures'));
    }

    public function share(Request $request)
    {
        $figures = Figure::active()
            ->with('category')
            ->orderByRaw('stock > 0 desc')
            ->orderBy('is_featured', 'desc')
            ->paginate(20);

        $categories = Category::active()
            ->whereHas('figures', function ($query) {
                $query->active();
            })
            ->get();

        $featured = collect();

        return view('catalog.index', compact('figures', 'categories', 'featured'))
            ->with('isShared', true);
    }
}
