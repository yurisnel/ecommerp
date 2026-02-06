@extends('layouts.shop')

@section('content')
<div class="container mx-auto px-6 py-12">
    <!-- Breadcrumb -->
    <nav class="flex text-sm text-gray-500 mb-8">
        <a href="{{ route('shop.home') }}" class="hover:text-blue-600">Home</a>
        <span class="mx-2">/</span>
        <a href="{{ route('shop.catalog') }}" class="hover:text-blue-600">Catalog</a>
        <span class="mx-2">/</span>
        @if($product->category)
            <a href="{{ route('shop.catalog', ['category' => $product->category->slug]) }}" class="hover:text-blue-600">{{ $product->category->name }}</a>
            <span class="mx-2">/</span>
        @endif
        <span class="text-gray-800">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
        <!-- Product Images -->
        <div class="space-y-4">
            <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden shadow-sm">
                @if($product->image)
                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-4xl">📷</div>
                @endif
            </div>
            <!-- Thumbnails placeholder (if multiple images) -->
            <div class="grid grid-cols-4 gap-4">
                <div class="aspect-square bg-gray-100 rounded-lg cursor-pointer ring-2 ring-blue-500"></div>
                <div class="aspect-square bg-gray-100 rounded-lg cursor-pointer hover:bg-gray-200"></div>
                <div class="aspect-square bg-gray-100 rounded-lg cursor-pointer hover:bg-gray-200"></div>
            </div>
        </div>

        <!-- Product Info -->
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
            <div class="flex items-center mb-6">
                <div class="flex text-yellow-400 text-sm">
                    ★★★★★
                </div>
                <span class="text-gray-400 text-sm ml-2">(No reviews yet)</span>
            </div>
            
            <div class="text-3xl font-bold text-blue-600 mb-6">$199.00</div> <!-- Dynamic Price -->

            <div class="prose prose-sm text-gray-600 mb-8">
                <p>{{ $product->description }}</p>
            </div>

            <form action="{{ route('cart.add') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <!-- Quantity -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                    <div class="flex items-center w-32 border border-gray-300 rounded-md">
                        <button type="button" class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-100 border-r border-gray-300" onclick="this.nextElementSibling.stepDown()">-</button>
                        <input type="number" name="quantity" value="1" min="1" class="flex-1 w-full text-center border-none focus:ring-0 p-0 h-10 appearance-none">
                        <button type="button" class="w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-100 border-l border-gray-300" onclick="this.nextElementSibling.stepUp()">+</button>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-8 py-4 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Add to Cart
                    </button>
                    <button type="button" class="w-14 h-14 flex items-center justify-center border-2 border-gray-200 rounded-lg text-gray-400 hover:text-red-500 hover:border-red-200 transition">
                        ♥
                    </button>
                </div>
            </form>

            <!-- Meta -->
            <div class="mt-8 pt-8 border-t border-gray-100 text-sm text-gray-500 space-y-2">
                <p><span class="font-medium text-gray-900">SKU:</span> {{ $product->sku }}</p>
                <p><span class="font-medium text-gray-900">Category:</span> {{ $product->category->name ?? 'None' }}</p>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="mt-20">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($relatedProducts as $related)
                    <div class="group">
                        <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden mb-4 relative">
                             @if($related->image)
                                <img src="{{ $related->image }}" class="w-full h-full object-cover">
                            @endif
                            <a href="{{ route('shop.product', $related->slug) }}" class="absolute inset-0 z-10"></a>
                        </div>
                        <h3 class="font-bold text-gray-900 group-hover:text-blue-600 transition">{{ $related->name }}</h3>
                        <p class="text-gray-500">$99.00</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
