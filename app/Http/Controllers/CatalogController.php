<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Funkomaceta;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Funkomaceta::active()
            ->inStock()
            ->with(['category', 'figure']);

        if ($request->has('category') && $request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $categories = Category::active()
            ->whereHas('funkomacetas', function ($query) {
                $query->active()->inStock();
            })
            ->get();

        $featured = Funkomaceta::active()
            ->inStock()
            ->featured()
            ->with(['category', 'figure'])
            ->limit(6)
            ->get();

        return view('catalog.index', compact('products', 'categories', 'featured'));
    }

    public function show(string $slug)
    {
        $product = Funkomaceta::where('slug', $slug)
            ->active()
            ->with(['category', 'figure'])
            ->firstOrFail();

        $relatedProducts = Funkomaceta::active()
            ->inStock()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('catalog.show', compact('product', 'relatedProducts'));
    }

    public function share(Request $request)
    {
        $token = $request->get('token');

        $products = Funkomaceta::active()
            ->inStock()
            ->with(['category', 'figure'])
            ->orderBy('is_featured', 'desc')
            ->paginate(20);

        $categories = Category::active()
            ->whereHas('funkomacetas', function ($query) {
                $query->active()->inStock();
            })
            ->get();

        return view('catalog.index', compact('products', 'categories'))
            ->with('isShared', true);
    }
}
