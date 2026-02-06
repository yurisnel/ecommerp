<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display the home page with featured products
     */
    public function index()
    {
        $featuredProducts = Product::where('status', 'active')
            ->latest()
            ->take(8)
            ->get();
            
        $categories = Category::where('parent_id', null)
            ->where('status', 'active')
            ->take(6)
            ->get();

        return view('shop.home', compact('featuredProducts', 'categories'));
    }

    /**
     * Display the product catalog
     */
    public function catalog(Request $request)
    {
        $query = Product::where('status', 'active');

        // Filter by category
        if ($request->has('category')) {
            $category = Category::where('slug', $request->category)->firstOrFail();
            $query->where('category_id', $category->id);
            // Optionally include subcategories logic here
        }

        // Search
        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $products = $query->paginate(12);
        $categories = Category::where('parent_id', null)->where('status', 'active')->get();

        return view('shop.catalog', compact('products', 'categories'));
    }

    /**
     * Display product details
     */
    public function product($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();
            
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('shop.product', compact('product', 'relatedProducts'));
    }
}
