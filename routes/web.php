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

// Assistant
Route::post('/create-assistants', [AiController::class,'createAssistance'])->name('createAssistant'); // Create OpenAI Assistant
Route::get('/assistants', [AiController::class,'listAssistants'])->name('assistant'); // List all created Assistants
Route::get('/assistants/{assistantId}', [AiController::class,'retrieveAssistant'])->name('retrieveAssistant'); // Select Assistants
Route::post('/assistants/{assistantId}', [AiController::class,'updateAssistant'])->name('updateAssistant'); // Update Selected Assistants
Route::get('/delete-assistants/{assistantId}', [AiController::class,'deleteAssistant'])->name('deleteAssistant'); // Delete selected Assistants

// Thread
Route::get('start-chat/{assistantId}', [AiController::class,'startChat'])->name('startChat');
Route::get('/thread/{assistantId}', [AiController::class,'createThread'])->name('createThread'); // Create Thread in Assistant
Route::get('/thread/{assistantId}/{id}', [AiController::class,'getThread'])->name('getThread'); // Retrieve thread info | id=threadId
// Route::get('/create-run-thread/{assistantId}', [AiController::class,'createAndRunThread'])->name('createAndRunThread'); // Create thread and run in single request

// Messages
Route::post('/create-message/{assistantId}/{threadId}', [AiController::class,'createMessage'])->name('createMessage'); // Create Message in selected thread
Route::get('/retrieve-message/{threadId}', [AiController::class, 'retrieveMessage'])->name('retrieveMessage');

// Runs
Route::get('/run-thread/{threadId}/{assistantId}', [AiController::class,'runThread'])->name('runThread'); // Run Thread
Route::get('/thread/{threadId}/run/{runId}', [AiController::class,'listRuns'])->name('listRuns'); // List all Runs
Route::get('/submit-run/{threadId}/{runId}', [AiController::class,'submitRun']); // Submit Run

// File Upload
Route::get('delete-file/{assistantId}/{fileId}', [AiController::class, 'deleteFile'])->name('deleteFile');
