<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Helpers\TokenHelpers;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase, TokenHelpers;

    public function test_can_get_all_products()
    {
        $products = Product::all();
        if (count($products) == 0) {
            Product::factory()->count(5)->create();
            $products = Product::all();
        }

        $response = $this->get(
            '/api/products',
            $this->authorization_header()
        );

        $response->assertJsonStructure([
            'products'
        ]);

        $this->assertCount($products->count(), $response->json()['products']);
    }

    public function test_can_get_specific_product()
    {
        $product = Product::inRandomOrder()->first();

        if (!$product) {
            Product::factory()->count(1)->create();
            $product = Product::inRandomOrder()->first();
        }

        $response = $this->getJson(
            '/api/products/' . $product->id,
            $this->authorization_header()
        );

        $response->assertExactJson([
            'id' => $product->id,
            'name' => $product->name
        ]);
    }

    public function test_can_create_product_succesfully()
    {
        $response = $this->postJson(
            '/api/products',
            [
                'name' => 'Maçã'
            ],
            $this->authorization_header()
        );

        $response->assertJson(fn (AssertableJson $json) =>
        $json->whereType('name', 'string')
             ->whereType('id', 'integer')
        );
    }

    public function test_can_edit_product_succesfully()
    {

        $response = $this->postJson(
            '/api/products',
            [
                'name' => 'Maçã'
            ],
            $this->authorization_header()
        );

        $product_id = json_decode($response->content(), true)['id'];

        $response = $this->patchJson(
            '/api/products/' . $product_id,
            [
                'name' => 'Produto Teste'
            ],
            $this->authorization_header()
        );

        $response->assertExactJson([
            'id' => $product_id,
            'name' => 'Produto Teste'
        ]);
    }

    public function test_can_delete_product_succesfully()
    {
        $product = Product::factory()->count(1)->create()->first();

        $response = $this->delete(
            '/api/products/' . $product->id,
            [],
            $this->authorization_header()
        );

        $response->assertStatus(204);
        $this->assertDeleted($product);
    }
}
