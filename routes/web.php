<?php

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

Route::get('/', function(){
		return redirect('/f');
		// return session('username');
		return view('front.index');
	});

//非常重要
Route::group(['prefix'=>'f','namespace'=>'f','middleware'=>'initial'],function(){
	Route::get('/{wa?}', function(){
		return view('front.index');
	});
});
