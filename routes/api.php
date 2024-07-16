<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;


Route::post('register',[AuthController::class,'register'])->name('register');
Route::post('login',[AuthController::class,'login'])->name('login');

Route::middleware(['auth:sanctum'])->group(function (){
    Route::get('/checkingAuthenticated', function() {
        return response()->json(['message'=>'you are in','status'=>200],200);
    });
    Route::post('logout',[AuthController::class,'logout'])->name('logout');
    
    //Category
    Route::post('store-category',[CategoryController::class,'store'])->name('store');
    Route::get('view-category',[CategoryController::class,'index'])->name('index');
    Route::post('update-category/{id}',[CategoryController::class,'update'])->name('updateCategory');
    Route::delete('delete-category/{id}',[CategoryController::class,'destroy'])->name('deleteCategory');
});