<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/', [AiController::class, 'index']);

Route::get('/assistants', [AiController::class,'createAssistance']); // Create OpenAI Assistant
Route::get('/list-assistants', [AiController::class,'listAssistants']); // List all created Assistants

Route::get('/thread', [AiController::class,'createThread']); // Create Thread in Assistant
Route::get('/thread/{id}', [AiController::class,'getThread']); // Retrieve thread info | id=threadId

Route::post('/create-message', [AiController::class,'createMessage']); // Create Message in selected thread

Route::get('/run-thread/{threadId}/{assistantId}', [AiController::class,'runThread']); // Run Thread
Route::get('/thread/{threadId}/run', [AiController::class,'listRuns']); // List all Runs
Route::get('/submit-run/{threadId}/{runId}', [AiController::class,'submitRun']); // Submit Run
