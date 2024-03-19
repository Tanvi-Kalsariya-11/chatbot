<?php

use App\Http\Controllers\GroupChatController;
use App\Http\Controllers\UserController;
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
    return view('login');
});

Route::controller(UserController::class)->group(function() {
    Route::get('/register', 'register')->name('register');
    Route::post('/store', 'store')->name('store');
    Route::get('/', 'login')->name('login');
    Route::post('/authenticate', 'authenticate')->name('authenticate');
    // Route::get('/dashboard', 'dashboard')->name('dashboard');
    Route::post('/logout', 'logout')->name('logout');
});

// Assistant

Route::group(['middleware' => 'auth'], function () {
    // Route::get('/assistants', [AiController::class,'listAssistants'])->name('assistant'); // List all created Assistants
    Route::post('/create-assistants', [AiController::class,'createAssistance'])->name('createAssistant'); // Create OpenAI Assistant
    Route::get('/assistant/{assistantId}', [AiController::class,'retrieveAssistant'])->name('retrieveAssistant'); // Select Assistants
    Route::post('/assistant/{assistantId}', [AiController::class,'updateAssistant'])->name('updateAssistant'); // Update Selected Assistants
    Route::get('/delete-assistants/{assistantId}', [AiController::class,'deleteAssistant'])->name('deleteAssistant'); // Delete selected Assistants
    
    // Thread
    Route::get('start-chat/{assistantId}', [AiController::class,'startChat'])->name('startChat'); // Start new chat
    Route::get('thread/{assistantId}', [AiController::class,'getLastThread'])->name('getLastThread'); // List all threads of an Assistant
    // Route::get('/thread/{assistantId}', [AiController::class,'createThread'])->name('createThread'); // Create Thread in Assistant
    Route::get('/thread/{assistantId}/{id}', [AiController::class,'getThread'])->name('getThread'); // Retrieve thread info | id=threadId
    Route::get('delete-thread/{assistantId}/{threadId}', [AiController::class,'deleteThread'])->name('deleteThread');
    
    // Messages
    Route::post('/create-message/{assistantId}/{threadId}', [AiController::class,'createMessage'])->name('createMessage'); // Create Message in selected thread
    Route::get('/retrieve-message/{threadId}', [AiController::class, 'retrieveMessage'])->name('retrieveMessage');
    
    // Runs
    Route::get('/run-thread/{threadId}/{assistantId}', [AiController::class,'runThread'])->name('runThread'); // Run Thread
    Route::get('/thread/{threadId}/run/{runId}', [AiController::class,'listRuns'])->name('listRuns'); // List all Runs
    Route::get('/submit-run/{threadId}/{runId}', [AiController::class,'submitRun']); // Submit Run
    
    // File Upload
    Route::get('delete-file/{assistantId}/{fileId}', [AiController::class, 'deleteFile'])->name('deleteFile');
    
    
    // AUTHENTICATED USER DATA
    Route::get('/user-assistant', [AiController::class, 'listUserAssistants'])->name('listUserAssistants');
    
    // Streaming
    Route::get('stream-response/{threadId}/{assistantId}', [AiController::class, 'streamResponse'])->name('streamResponse');
    


    // Group
    // Route::view('/group', 'chatGroup');
    Route::get('/group', [GroupChatController::class,'listGroups'])->name('listGroups');
    Route::get('/select-assistant', [GroupChatController::class,'selectAssistant'])->name('selectAssistant');
    Route::post('/create-group', [GroupChatController::class,'save'])->name('createGroup');
    Route::get('/group/{groupId}', [GroupChatController::class,'getGroup'])->name('getGroup');
    Route::post('/group/{groupId}', [GroupChatController::class,'update'])->name('updateGroup');
    
    Route::get('/group-chat/{groupId}', [GroupChatController::class,'groupChat'])->name('groupChat');
    Route::get('/accept-invite/{groupId}', [GroupChatController::class,'acceptGroupInvite'])->name('acceptGroupInvite');

    // Messages
    Route::post('send-message/{groupId}', [GroupChatController::class, 'sendMessage'])->name('sendMessage');
    Route::get('/retrieve-group-messages/{groupId}',[GroupChatController::class,'retrieveGroupChat'])->name('retrieveGroupChat');
    Route::get('/latestTimestamp/{groupId}', [GroupChatController::class , 'latestTimestamp'])->name('latestTimestamp');

    
    // Web Socket
    Route::get('/websocket', [GroupChatController::class, 'websocket']);
    Route::view('chat-websocket','chatWebsocket');

});