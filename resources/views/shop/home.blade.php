@extends('layouts.shop')

@section('content')
<!-- Hero Section -->
<div class="relative bg-gray-900 text-white overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-gray-900 to-transparent z-10"></div>
    <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Hero" class="absolute inset-0 w-full h-full object-cover opacity-50">
    
    <div class="container mx-auto px-6 py-24 relative z-20">
        <div class="max-w-2xl">
            <h1 class="text-5xl font-bold mb-6 leading-tight">Elevate Your Lifestyle with Premium Goods</h1>
            <p class="text-lg text-gray-300 mb-8">Discover our curated collection of high-quality products designed to inspire and delight.</p>
            <a href="{{ route('shop.catalog') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-md font-semibold transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                Shop Now
            </a>
        </div>
    </div>
</div>

<!-- Featured Categories -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-6">
        <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">Shop by Category</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
            @foreach($categories as $category)
                <a href="{{ route('shop.catalog', ['category' => $category->slug]) }}" class="group block text-center">
                    <div class="bg-gray-100 rounded-full h-32 w-32 mx-auto mb-4 flex items-center justify-center group-hover:bg-blue-50 transition duration-300">
                        <!-- Placeholder Icon/Image -->
                        <span class="text-4xl">📦</span>
                    </div>
                    <h3 class="font-medium text-gray-800 group-hover:text-blue-600 transition">{{ $category->name }}</h3>
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-6">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Featured Products</h2>
                <p class="text-gray-600">Handpicked selections just for you</p>
            </div>
            <a href="{{ route('shop.catalog') }}" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                View All <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($featuredProducts as $product)
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition duration-300 group overflow-hidden border border-gray-100">
                    <div class="relative h-64 bg-gray-200 overflow-hidden">
                        @if($product->image)
                            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                                📷 No Image
                            </div>
                        @endif
                        
                        <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-2">
                             <a href="{{ route('shop.product', $product->slug) }}" class="bg-white text-gray-900 p-2 rounded-full hover:bg-blue-600 hover:text-white transition shadow-lg transform translate-y-4 group-hover:translate-y-0 duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                             </a>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-sm text-gray-500 mb-1">{{ $product->category->name ?? 'Category' }}</p>
                        <h3 class="font-bold text-gray-900 mb-2 truncate">
                            <a href="{{ route('shop.product', $product->slug) }}" class="hover:text-blue-600 transition">{{ $product->name }}</a>
                        </h3>
                        <div class="flex justify-between items-center">
                            <span class="text-blue-600 font-bold">$199.00</span> <!-- Placeholder Price -->
                            <form action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="bg-gray-100 text-gray-600 p-2 rounded-lg hover:bg-blue-600 hover:text-white transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-20 bg-blue-600 text-white">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl font-bold mb-4">Join Our Community</h2>
        <p class="mb-8 text-blue-100 max-w-2xl mx-auto">Subscribe for exclusive offers, new product alerts, and lifestyle inspiration.</p>
        <form class="max-w-md mx-auto flex">
            <input type="email" placeholder="Enter your email" class="flex-1 px-4 py-3 rounded-l-md text-gray-900 focus:outline-none">
            <button class="bg-gray-900 px-6 py-3 rounded-r-md font-semibold hover:bg-gray-800 transition">Subscribe</button>
        </form>
    </div>
</section>
@endsection
