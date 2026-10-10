<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unauthenticated user cannot access products list', function () {
    $response = $this->get(route('products.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated user can view products list', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);
    Product::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('products.index'));

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Products/Index')->has('products.data', 3));
});

test('authenticated user can create a product', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);

    $response = $this->actingAs($user)->post(route('products.store'), [
        'name' => 'Test Smart Watch',
        'sku' => 'SKU-WATCH-001',
        'category' => 'Electronics',
        'price' => '199.99',
        'stock' => 15,
        'status' => 'active',
        'description' => 'A great smart watch with OLED display.',
    ]);

    $response->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'user_id' => $user->id,
        'name' => 'Test Smart Watch',
        'sku' => 'SKU-WATCH-001',
        'category' => 'Electronics',
        'price' => 199.99,
        'stock' => 15,
    ]);
});

test('product creation validates required fields', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);

    $response = $this->actingAs($user)->post(route('products.store'), []);

    $response->assertSessionHasErrors(['name', 'sku', 'category', 'price', 'stock']);
});

test('user can update their own product', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $product = Product::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

    $response = $this->actingAs($user)->put(route('products.update', $product->id), [
        'name' => 'Updated Product Name',
        'sku' => $product->sku,
        'category' => $product->category,
        'price' => '250.00',
        'stock' => 20,
        'status' => 'active',
        'description' => $product->description,
    ]);

    $response->assertRedirect(route('products.index'));
    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Product Name',
        'price' => 250.00,
    ]);
});

test('user cannot update another user product', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    giveActiveSubscription($userB);
    $product = Product::factory()->create(['user_id' => $userA->id]);

    $response = $this->actingAs($userB)->put(route('products.update', $product->id), [
        'name' => 'Hacked Name',
        'sku' => $product->sku,
        'category' => $product->category,
        'price' => '1.00',
        'stock' => 1,
        'status' => 'active',
    ]);

    $response->assertStatus(403);
});

test('user can delete their own product', function () {
    $user = User::factory()->create();
    giveActiveSubscription($user);
    $product = Product::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete(route('products.destroy', $product->id));

    $response->assertRedirect(route('products.index'));
    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('user cannot delete another user product', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    giveActiveSubscription($userB);
    $product = Product::factory()->create(['user_id' => $userA->id]);

    $response = $this->actingAs($userB)->delete(route('products.destroy', $product->id));

    $response->assertStatus(403);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
});
