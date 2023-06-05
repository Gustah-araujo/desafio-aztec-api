<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\TokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use OpenApi\Annotations as OA;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/token', [TokenController::class, 'issue_token']);

Route::middleware('token.auth')->group(function() {

    // Product Routes
    Route::controller(ProductController::class)->group( function() {

        Route::get('/products', 'index');
        Route::get('/products/{id}', 'show');
        Route::post('/products', 'store');
        Route::patch('/products/{id}', 'update');
        Route::delete('/products/{id}', 'destroy');

    });

    // Product Routes
    Route::controller(ShoppingListController::class)->group( function() {

        Route::get('/shopping_lists', 'index');
        Route::get('/shopping_lists/{id}', 'show');
        Route::post('/shopping_lists', 'store');
        Route::patch('/shopping_lists/{id}', 'update');
        Route::delete('/shopping_lists/{id}', 'destroy');
        Route::post('/shopping_lists/{id}/products', 'add_product');
        Route::patch('/shopping_lists/{id}/products/{product_id}', 'edit_product');

    });

});
