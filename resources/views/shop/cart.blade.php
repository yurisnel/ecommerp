@extends('layouts.shop')

@section('content')
<div class="container mx-auto px-6 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

    @if(count($cart) > 0)
        <div class="flex flex-col lg:flex-row gap-12">
            <!-- Cart Items -->
            <div class="flex-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-500 text-sm uppercase">
                            <tr>
                                <th class="px-6 py-4 font-medium">Product</th>
                                <th class="px-6 py-4 font-medium">Price</th>
                                <th class="px-6 py-4 font-medium">Quantity</th>
                                <th class="px-6 py-4 font-medium text-right">Total</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($cart as $id => $details)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-16 w-16 flex-shrink-0 bg-gray-100 rounded-md overflow-hidden mr-4">
                                                @if($details['image'])
                                                    <img src="{{ $details['image'] }}" class="h-full w-full object-cover">
                                                @else
                                                     <div class="h-full w-full flex items-center justify-center text-gray-400">📷</div>
                                                @endif
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-gray-900">{{ $details['name'] }}</h3>
                                                <p class="text-sm text-gray-500">Ref: {{ $id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">${{ number_format($details['price'], 2) }}</td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('cart.update', $id) }}" method="POST" class="flex items-center">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" name="quantity" value="{{ $details['quantity'] }}" min="1" class="w-16 border-gray-300 rounded-md text-center text-sm focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900">
                                        ${{ number_format($details['price'] * $details['quantity'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('cart.remove', $id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-500 hover:text-red-700 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-6">
                    <a href="{{ route('shop.catalog') }}" class="text-blue-600 font-medium hover:text-blue-800 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Continue Shopping
                    </a>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="w-full lg:w-96">
                <div class="bg-gray-50 rounded-xl p-8 sticky top-24 border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Order Summary</h2>
                    
                    <div class="space-y-4 mb-6 pb-6 border-b border-gray-200 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span class="text-green-600">Calculated at checkout</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax</span>
                            <span class="text-gray-500">--</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mb-8">
                        <span class="font-bold text-lg text-gray-900">Total</span>
                        <span class="font-bold text-2xl text-blue-600">${{ number_format($total, 2) }}</span>
                    </div>

                    <button class="w-full bg-gray-900 text-white py-4 rounded-lg font-bold hover:bg-gray-800 transition shadow-lg">
                        Proceed to Checkout
                    </button>
                    
                    <p class="text-xs text-center text-gray-500 mt-4 px-4">
                        Taxes and shipping calculated at checkout.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-20 bg-gray-50 rounded-xl">
            <div class="text-6xl mb-6">🛒</div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Your cart is empty</h2>
            <p class="text-gray-500 mb-8">Looks like you haven't added anything to your cart.</p>
            <a href="{{ route('shop.catalog') }}" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700 transition">
                Start Shopping
            </a>
        </div>
    @endif
</div>
@endsection
