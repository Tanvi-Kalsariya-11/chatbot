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

Route::get('/assistants', function () {
    return view('assistant');
});

// Route::get('/chatbot', [AiController::class, 'index'])->name('chatbot');

Route::post('/create-assistants', [AiController::class,'createAssistance'])->name('createAssistant'); // Create OpenAI Assistant
Route::get('/assistants', [AiController::class,'listAssistants'])->name('assistant'); // List all created Assistants
Route::get('/assistants/{assistantId}', [AiController::class,'retrieveAssistant'])->name('retrieveAssistant'); // Select Assistants
Route::post('/assistants/{assistantId}', [AiController::class,'updateAssistant'])->name('updateAssistant'); // Update Selected Assistants
Route::get('/delete-assistants/{assistantId}', [AiController::class,'deleteAssistant'])->name('deleteAssistant'); // Delete selected Assistants

Route::get('/thread/{assistantId}', [AiController::class,'createThread'])->name('createThread'); // Create Thread in Assistant
Route::get('/thread/{assistantId}/{id}', [AiController::class,'getThread'])->name('getThread'); // Retrieve thread info | id=threadId

Route::post('/create-message/{assistantId}/{threadId}', [AiController::class,'createMessage'])->name('createMessage'); // Create Message in selected thread
Route::get('/retrieve-message/{threadId}', [AiController::class, 'retrieveMessage']);

// Route::get('/create-and-run', [AiController::class,'createAndRunThread'])->name('createAndRun'); // Create Message in selected thread and run that thread

Route::get('/run-thread/{threadId}/{assistantId}', [AiController::class,'runThread'])->name('runThread'); // Run Thread
Route::get('/thread/{threadId}/run/{runId}', [AiController::class,'listRuns']); // List all Runs
Route::get('/submit-run/{threadId}/{runId}', [AiController::class,'submitRun']); // Submit Run
