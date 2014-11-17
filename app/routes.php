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

Route::get('/', function()
{
    return View::make('live')->withDefaultList(Config::get('todo.gsd.defaultList'));
});

Route::get('mock', function(){
    return View::make('mockup');
});

Route::resource('lists', 'GSD\Controllers\ListController', array('except'=>array('create', 'edit')));

Route::post('lists/{lists}/archive', array('as'=>'lists.archive', 'uses'=>'GSD\Controllers\ListController@archive'));

Route::post('lists/{lists}/unarchive', array('as'=>'lists.unarchive', 'uses'=>'GSD\Controllers\ListController@unarchive'));

Route::post('lists/{source}/rename/{dest}', array('as'=>'lists.rename', 'uses'=>'GSD\Controllers\ListController@rename'));

Route::post('lists/{source}/{index}/move/{dest}', 'GSD\Controllers\ListController@moveTask');
