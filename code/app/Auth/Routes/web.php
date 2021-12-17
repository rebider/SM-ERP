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

// 登录
Route::get('/login', 'LoginController@index')->name("login");
Route::any('/forget', 'RegisterController@forget')->name("forget");
Route::any('/register', 'RegisterController@register')->name("register");
Route::any('/checked', 'CheckedServiceController@checked')->name("checked");
Route::post('/auth/doLogin', 'LoginController@doLogin');
Route::get('/auth/verifyCode', 'LoginController@verifyCode');
Route::get('/auth/login/requiredVerifyCode', 'LoginController@requiredVerifyCode');
Route::get('/logout', 'LoginController@logout')->name("logout");

// 用户
Route::group(['prefix' => 'user'], function(){
    //子账户列表
    Route::any('index', 'UsersController@index');
    Route::any('search', 'UsersController@search');
    Route::any('add', 'UsersController@add');
    Route::any('edit/{id}', 'UsersController@edit');
    //个人中心
    Route::any('detail/{id}', 'UsersController@detail');
    Route::post('editPassword', 'UsersController@editPassword');
    //用户删除
    Route::any('del', 'UsersController@delUser');
    //菜单权限
    Route::any('menus/{id}', 'UsersController@menus');
    //店铺权限
    Route::any('shops/{id}', 'UsersController@shops');
});

