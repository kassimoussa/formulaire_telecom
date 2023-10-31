<?php

use App\Http\Controllers\StocksController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* Route::get('/', function () {
    return view('auth.connexion');
}); */

Route::get('/', [UsersController::class, 'accueil']);
Route::prefix('auth')->group(function () {
    Route::post('/check', [UsersController::class, 'check']);

});    


Route::group(['middleware'=> ['logged']], function(){
    Route::get('/index', [UsersController::class, 'index']);
    Route::get('/logout', [UsersController::class, 'logout']);

    Route::prefix('stocks')->group(function () {
        Route::get('/', [StocksController::class, 'index']);
        Route::get('/{site}', [StocksController::class, 'stocksite']);
        Route::get('/rentree/{stock}', [StocksController::class, 'rentree']);
        Route::get('/sortie/{stock}', [StocksController::class, 'sortie']);
        Route::get('/allrentrees/{site}', [StocksController::class, 'allrentrees']);
        Route::get('/allsorties/{site}', [StocksController::class, 'allsorties']);
    
    });  

    Route::get('/materiels/show/{materiel}', [StocksController::class, 'showMateriel']);

    Route::prefix('admin')->group(function (){
        Route::get('/', [UsersController::class, 'liste']);
    });


});    

