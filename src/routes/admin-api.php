<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Jxgame\Aibisai\Controllers\Api\Admin;

Route::group(['namespace' => 'Admin', 'prefix' => 'admin'], function () { 
	Route::get('init', function () { return "welcome api admin.";});
    Route::get('index/init', [Admin\IndexController::class, 'init'])->name('admin.index.init');
});
