<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

// Main index
Route::get('/', 'ViewController@showIndex');

// Admin Routes
Route::group(array('before' => 'auth.basic'), function(){
	Route::resource('admin', 'AdminController');
});

// detail route filter
Route::filter('postExists', function($route) {
	// get route name param
	$name = $route->getParameter('name');
	// see if it exists
	$post = Post::where('filename', $name)->first();
	if (is_null($post))
	{
	   return Redirect::to('/');
	}
});

// detail routes
Route::get('{name}', array(
	'as' 	 => 'post',
	'uses'	 => 'ViewController@showDetail',
	'before' => 'postExists'))
		->where('name', '[a-z]+');