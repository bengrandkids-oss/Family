<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Family member  management routes
Route::post('/add-family-member', [App\Http\Controllers\FamilyMembersController::class, 'store']);
Route::get('/family-members', [App\Http\Controllers\FamilyMembersController::class, 'index']);
Route::get('/family-members/{member_id}', [App\Http\Controllers\FamilyMembersController::class, 'show']);
Route::put('/family-members/update/{member_id}', [App\Http\Controllers\FamilyMembersController::class, 'update']);
Route::delete('/family-members/delete/{member_id}', [App\Http\Controllers\FamilyMembersController::class, 'destroy']);

// Route::apiResource('family-members', App\Http\Controllers\FamilyMembersController::class);

// Relations management routes
Route::post('/add-relation', [App\Http\Controllers\RelationsController::class, 'store']);
Route::get('/relations', [App\Http\Controllers\RelationsController::class, 'index']);
Route::get('/relations/{member_id}', [App\Http\Controllers\RelationsController::class, 'show']);
Route::put('/relations/update/{relation_id}', [App\Http\Controllers\RelationsController::class, 'update']);
Route::delete('/relations/delete/{relation_id}', [App\Http\Controllers\RelationsController::class, 'destroy']);  


// Family management routes
Route::post('/add-family', [App\Http\Controllers\FamiliesController::class, 'store']);
Route::get('/families', [App\Http\Controllers\FamiliesController::class, 'index']);
Route::get('/families/{family_id}', [App\Http\Controllers\FamiliesController::class, 'show']);
Route::put('/families/update/{family_id}', [App\Http\Controllers\FamiliesController::class, 'update']); // todo
Route::delete('/families/delete/{family_id}', [App\Http\Controllers\FamiliesController::class, 'destroy']); //todo