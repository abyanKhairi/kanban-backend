<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\TaskController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::get('me', [AuthController::class, 'me']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('register', [AuthController::class, 'register']);
});


Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'kanban'
], function ($router) {
    Route::post('board', [BoardController::class, 'store']);
    Route::post('board-member/{board}', [BoardController::class, 'addMember']);
    Route::get('board-list', [BoardController::class, 'index']);
    Route::put('board-update/{board}', [BoardController::class, 'update']);
    Route::delete('board-delete/{board}', [BoardController::class, 'destroy']);
    Route::get('board-show/{board}', [BoardController::class, 'show']);
    Route::get('board-four', [BoardController::class, 'showFourBoard']);
});

Route::group(
    [
        'middleware' => 'auth:api',
        'prefix' => 'kanban/board'
    ],
    function ($router) {
        Route::get('column-list/{board}', [ColumnController::class, 'index']);
        Route::post('column/{board}', [ColumnController::class, 'store']);
        Route::put('column-update/{column}', [ColumnController::class, 'update']);
        Route::put('column-position/{column}', [ColumnController::class, 'position']);
        Route::delete('column-delete/{column}', [ColumnController::class, 'destroy']);
    }
);

Route::group(
    [
        'middleware' => 'auth:api',
        'prefix' => 'kanban/board/column-task'
    ],
    function ($router) {
        Route::get('task-list', [TaskController::class, 'index']);
        Route::post('task', [TaskController::class, 'store']);
        Route::put('task-update/{task}', [TaskController::class, 'update']);
        Route::put('task-position/{task}', [TaskController::class, 'position']);
        Route::put('task-column/{task}', [TaskController::class, 'column']);
        Route::delete('task-delete/{task}', [TaskController::class, 'destroy']);
    }
);
