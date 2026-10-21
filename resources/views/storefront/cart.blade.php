@extends('layouts.storefront')
@section('title', 'Your cart | '.config('app.name'))
@section('content')
<section class="mx-auto max-w-5xl min-w-0 px-4 py-8 sm:px-6 sm:py-12">
    <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Your bag</p><h1 class="mt-2 text-3xl font-black sm:text-4xl">Shopping cart</h1>
    <div data-cart-root class="mt-8"></div>
</section>
@endsection
