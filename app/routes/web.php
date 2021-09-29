<?php

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

Auth::routes();

Route::get('importar', 'ImportarController@index')->name('home')->middleware('auth');

Route::get('prepara', 'ImportarController@PreparaImagens')->name('preparaimg')->middleware('auth');

Route::get('processar', 'ProcessarController@processar')->name('processar')->middleware('auth');

Route::get('enviaimagem', 'EnviarMagentoController@index')->name('enviaimagem')->middleware('auth');


Route::resource('/teste','testeController')->middleware('auth');

Route::resource('/','ProdutosController')->middleware('auth');
Route::resource('/home','ProdutosController')->middleware('auth');
Route::resource('/produtos','ProdutosController')->middleware('auth');

Route::resource('ImagensBases','ImagensBasesController')->middleware('auth');


Route::resource('register','ProdutosController')->middleware('auth');
