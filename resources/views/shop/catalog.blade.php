@extends('layouts.shop')

@section('content')
<div class="container mx-auto px-6 py-12">
    <div class="flex flex-col md:flex-row gap-12">
        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 flex-shrink-0">
            <div class="mb-8">
                <h3 class="text-lg font-bold mb-4 border-b pb-2">Categories</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('shop.catalog') }}" class="{{ !request('category') ? 'text-blue-600 font-bold' : 'text-gray-600 hover:text-blue-600' }}">All Products</a>
                    </li>
                    @foreach($categories as $category)
                        <li>
                            <a href="{{ route('shop.catalog', ['category' => $category->slug]) }}" class="{{ request('category') == $category->slug ? 'text-blue-600 font-bold' : 'text-gray-600 hover:text-blue-600' }}">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            
            <!-- More filters (Price, Brand, etc) can go here -->
        </aside>

        <!-- Product Grid -->
        <div class="flex-1">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ request('category') ? ucfirst(request('category')) : 'All Products' }}
                </h1>
                
                <div class="flex items-center text-sm text-gray-500">
                    <span class="mr-2">Sort by:</span>
                    <select class="border-gray-300 border rounded p-1 focus:ring-blue-500 focus:border-blue-500">
                        <option>Newest</option>
                        <option>Price: Low to High</option>
                        <option>Price: High to Low</option>
                    </select>
                </div>
            </div>

            @if($products->isEmpty())
                <div class="text-center py-20 bg-gray-50 rounded-lg">
                    <p class="text-gray-500 text-lg">No products found in this category.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($products as $product)
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition duration-300 group overflow-hidden border border-gray-100">
                            <!-- Use same product card style as home -->
                            <div class="relative h-64 bg-gray-200 overflow-hidden">
                                @if($product->image)
                                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">📷</div>
                                @endif
                                <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                     <a href="{{ route('shop.product', $product->slug) }}" class="bg-white text-gray-900 px-4 py-2 rounded-full font-medium hover:bg-blue-600 hover:text-white transition shadow-lg transform translate-y-4 group-hover:translate-y-0 duration-300">
                                        View Details
                                     </a>
                                </div>
                            </div>
                            <div class="p-6">
                                <p class="text-sm text-gray-500 mb-1">{{ $product->category->name ?? 'Uncategorized' }}</p>
                                <h3 class="font-bold text-gray-900 mb-2 truncate">{{ $product->name }}</h3>
                                <div class="flex justify-between items-center">
                                    <span class="text-blue-600 font-bold">$199.00</span>
                                    <form action="{{ route('cart.add') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button class="text-blue-600 hover:text-blue-800 font-medium text-sm">Add to Cart</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
