<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);
        $filters = $request->only(['search', 'category', 'status']);

        $products = Product::with('user')
            ->filter($filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Product::select('category')->distinct()->pluck('category');

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Product::class);

        return Inertia::render('Products/Create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $request->user()->products()->create($request->validated());

        return redirect()->route('products.index')->with('success', __('flash.product_created'));
    }

    public function show(Product $product): Response
    {
        Gate::authorize('view', $product);

        return Inertia::render('Products/Show', [
            'product' => $product->load('user'),
        ]);
    }

    public function edit(Product $product): Response
    {
        Gate::authorize('update', $product);

        return Inertia::render('Products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $product->update($request->validated());

        return redirect()->route('products.index')->with('success', __('flash.product_updated'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $product->delete();

        return redirect()->route('products.index')->with('success', __('flash.product_deleted'));
    }
}
