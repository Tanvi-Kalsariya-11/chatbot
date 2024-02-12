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
    return view('chatbot');
});

// Route::get('/chatbot', [AiController::class, 'index'])->name('chatbot');

Route::get('/assistants', [AiController::class,'createAssistance']); // Create OpenAI Assistant
Route::get('/list-assistants', [AiController::class,'listAssistants']); // List all created Assistants

Route::get('/thread', [AiController::class,'createThread']); // Create Thread in Assistant
Route::get('/thread/{id}', [AiController::class,'getThread'])->name('getThread'); // Retrieve thread info | id=threadId

Route::post('/create-message', [AiController::class,'createMessage'])->name('createMessage'); // Create Message in selected thread

// Route::get('/create-and-run', [AiController::class,'createAndRunThread'])->name('createAndRun'); // Create Message in selected thread and run that thread

Route::get('/run-thread/{threadId}/{assistantId}', [AiController::class,'runThread'])->name('runThread'); // Run Thread
Route::get('/thread/{threadId}/run', [AiController::class,'listRuns']); // List all Runs
Route::get('/submit-run/{threadId}/{runId}', [AiController::class,'submitRun']); // Submit Run
