<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ShoppingList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Helpers\TokenHelpers;
use Tests\TestCase;

class ShoppingListTest extends TestCase
{
    use RefreshDatabase, TokenHelpers, WithFaker;

    public function test_can_get_all_shopping_lists()
    {
        $shopping_lists = ShoppingList::all();
        if (count($shopping_lists) == 0) {
            ShoppingList::factory()->count(5)->create();
            $shopping_lists = ShoppingList::all();
        }

        $response = $this->get(
            '/api/shopping_lists',
            $this->authorization_header()
        );

        $this->assertCount($shopping_lists->count(), $response->json());
    }

    public function test_can_get_specific_shopping_list()
    {
        $shopping_list = ShoppingList::inRandomOrder()->first();

        if (!$shopping_list) {
            ShoppingList::factory()->count(1)->create();
            $shopping_list = ShoppingList::inRandomOrder()->first();
        }


        $response = $this->getJson(
            '/api/shopping_lists/' . $shopping_list->id,
            $this->authorization_header()
        );

        $shopping_list = [
            'id' => $shopping_list->id,
            'title' => $shopping_list->title,
            'products' => $shopping_list->formatted_products()
        ];

        $response->assertExactJson($shopping_list);
    }

    public function test_can_create_shopping_list_without_products()
    {
        $response = $this->postJson(
            '/api/shopping_lists',
            [
                'title' => 'Test Shopping List'
            ],
            $this->authorization_header()
        );

        $response->assertJson(fn (AssertableJson $json) =>
        $json->whereType('title', 'string')
             ->whereType('id', 'integer')
             ->whereType('products', 'array')
        );
    }

    public function test_can_create_shopping_list_with_products()
    {
        $data = [];
        $validation_data = [];
        $products = Product::factory()->count(3)->create();

        foreach ($products as $product) {
            $quantity = rand(1, 99);

            $data[] = [
                'id' => $product->id,
                'quantity' => $quantity
            ];

            $validation_data[] = [
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => $quantity
            ];
        }

        $response = $this->postJson(
            '/api/shopping_lists',
            [
                'title' => 'Test Shopping List',
                'products' => $data
            ],
            $this->authorization_header()
        );

        $response->assertJson(fn (AssertableJson $json) =>
        $json->whereType('title', 'string')
             ->whereType('id', 'integer')
             ->whereType('products', 'array')
        );

        $this->assertSame($validation_data, $response->json()['products']);
    }

    public function test_can_edit_shopping_list()
    {
        $shopping_list = ShoppingList::factory()->count(1)->create()->first();
        $new_title = $this->faker->word();

        $validation_list = [
            'id' => $shopping_list->id,
            'title' => $new_title,
            'products' => $shopping_list->formatted_products()
        ];

        $response = $this->patchJson(
            '/api/shopping_lists/' . $shopping_list->id,
            [
                'title' => $new_title
            ],
            $this->authorization_header()
        );

        $this->assertSame($validation_list, $response->json());
    }

    public function test_can_add_product_to_shopping_list()
    {
        $shopping_list = ShoppingList::factory()->count(1)->create()->first();
        $validation_list = [
            'id' => $shopping_list->id,
            'title' => $shopping_list->title,
            'products' => $shopping_list->formatted_products()
        ];

        $response = $this->get(
            '/api/shopping_lists/' . $shopping_list->id,
            $this->authorization_header()
        );

        $this->assertSame($validation_list, $response->json());

        $product = Product::factory()->count(1)->create()->first();

        $response = $this->postJson(
            '/api/shopping_lists/' . $shopping_list->id . '/products',
            [
                'product_id' => $product->id,
                'quantity' => 1
            ],
            $this->authorization_header()
        );

        $validation_list = [
            'id' => $shopping_list->id,
            'title' => $shopping_list->title,
            'products' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => 1
                ]
            ]
        ];

        $this->assertSame($validation_list, $response->json());
    }

    public function test_can_change_product_quantity_in_shopping_list()
    {
        $shopping_list = ShoppingList::factory()->count(1)->create()->first();
        $validation_list = [
            'id' => $shopping_list->id,
            'title' => $shopping_list->title,
            'products' => $shopping_list->formatted_products()
        ];

        $response = $this->get(
            '/api/shopping_lists/' . $shopping_list->id,
            $this->authorization_header()
        );

        $this->assertSame($validation_list, $response->json());

        $product = Product::factory()->count(1)->create()->first();

        $response = $this->postJson(
            '/api/shopping_lists/' . $shopping_list->id . '/products',
            [
                'product_id' => $product->id,
                'quantity' => 1
            ],
            $this->authorization_header()
        );

        $validation_list = [
            'id' => $shopping_list->id,
            'title' => $shopping_list->title,
            'products' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => 1
                ]
            ]
        ];

        $this->assertSame($validation_list, $response->json());

        $shopping_list->refresh();

        $response = $this->patchJson(
            '/api/shopping_lists/' . $shopping_list->id . '/products/' . $product->id,
            [
                'quantity' => 2
            ],
            $this->authorization_header()
        );

        $validation_list = [
            'id' => $shopping_list->id,
            'title' => $shopping_list->title,
            'products' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => 2
                ]
            ]
        ];

        $this->assertSame($validation_list, $response->json());
    }

    public function test_can_delete_shopping_list()
    {
        $shopping_list = ShoppingList::factory()->count(1)->create()->first();
        $validation_list = [
            'id' => $shopping_list->id,
            'title' => $shopping_list->title,
            'products' => $shopping_list->formatted_products()
        ];

        $response = $this->get(
            '/api/shopping_lists/' . $shopping_list->id,
            $this->authorization_header()
        );

        $this->assertSame($validation_list, $response->json());

        $response = $this->delete(
            '/api/shopping_lists/' . $shopping_list->id,
            [],
            $this->authorization_header()
        );

        $response->assertStatus(204);
        $this->assertDeleted($shopping_list);
    }
}
