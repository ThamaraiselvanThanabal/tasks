<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/user', 'App\Http\Controllers\UserController@createUser'); 
Route::post('/tasks', 'App\Http\Controllers\TaskController@createTask'); 
Route::put('/tasks/{taskId}', 'App\Http\Controllers\TaskController@updateTask'); 
Route::delete('/tasks/{taskId}', 'App\Http\Controllers\TaskController@deleteTask'); 
Route::get('/tasks', 'App\Http\Controllers\TaskController@index'); 

Route::post('/tasks/{taskId}/assign', 'App\Http\Controllers\TaskController@assignTask');
Route::get('/users/{userId}/tasks', 'App\Http\Controllers\TaskController@getUserAssignedTasks');
Route::put('/tasks/{taskId}/progress', 'App\Http\Controllers\TaskController@setTaskProgress');

Route::get('/tasks/overdue', 'App\Http\Controllers\TaskController@getOverdueTasks');

Route::get('/tasks/status/{status}', 'App\Http\Controllers\TaskController@getTasksByStatus');
Route::get('/tasks/completed', 'App\Http\Controllers\TaskController@getCompletedTasksByDateRange');
Route::get('/tasks/statistics', 'App\Http\Controllers\TaskController@getTasksStatistics');
