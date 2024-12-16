<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
// use Illuminate\Container\Attributes\Auth;

Route::get('/', function () {
    return view('welcome');
});



Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
Route::get('/create',[App\Http\Controllers\HomeController::class, 'create'])->name('create');
Route::post('/store',[App\Http\Controllers\HomeController::class,'store'])->name('store');
Route::get('/get/data',[App\Http\Controllers\HomeController::class,'getData'])->name('get-data');
Route::get('/edit/{id}',[App\Http\Controllers\HomeController::class,'edit'])->name('edit');
Route::put('/update/{id}', [App\Http\Controllers\HomeController::class, 'update'])->name('update');
Route::delete('/delete/{id}', [App\Http\Controllers\HomeController::class, 'destroy'])->name('delete');


Route::resource('/student',StudentController::class);
Route::get('/get-student-data',[App\Http\Controllers\StudentController::class,'getStudentData'])->name('get-student-data') ;
