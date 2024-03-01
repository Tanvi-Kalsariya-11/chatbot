<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Thread;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use OpenAI;

class AiController extends Controller
{
    private $openaiApiKey;
    private $client;

    public function __construct()
    {
        $this->openaiApiKey = env('OPENAI_API_KEY');

        $yourApiKey = $this->openaiApiKey;
        $this->client = OpenAI::client($yourApiKey);
    }

    public function index()
    {
        return view("chatbot");
    }

    public function createAssistance(Request $request)
    {
        $request->validate([
            'assistantName' => 'required',
            'assistantInstruction' => 'required'
        ]);

        if ($request->hasFile('uploadFile')) {
            $assistantFile = $this->uploadFile($request->file('uploadFile'));

            // Create assistant with the attached file
            $this->client->assistants()->create([
                'instructions' => $request->assistantInstruction,
                'name' => $request->assistantName,
                'tools' => [
                    [
                        'type' => 'retrieval',
                    ],
                ],
                'model' => 'gpt-4-1106-preview',
                'file_ids' => [$assistantFile['id']],
            ]);
        } else {
            $this->client->assistants()->create([
                'instructions' => $request->assistantInstruction,
                'name' => $request->assistantName,
                'tools' => [
                    [
                        'type' => 'retrieval',
                    ],
                ],
                'model' => 'gpt-4-1106-preview',
            ]);
        }

        // $assistant = $response->toArray();
        return redirect()->route('assistant'); // ['id' => 'asst_VAGZ8DjGncGKfLCBojPPJXVU', ...]
    }

    public function listAssistants()
    {
        $response = $this->client->assistants()->list();

        $assistant = $response->toArray();
        // return route('assistant', ['assistant'=> $assistant]);
        return view('assistant', ['assistants' => $assistant['data']]);
        // return response()->json($assistant);
    }

    public function retrieveAssistant($assistantId)
    {
        $retrieveResponse = $this->client->assistants()->retrieve($assistantId);
        $listResponse = $this->client->assistants()->list();

        $assistantFile = [];
        $files = $retrieveResponse->fileIds;
        foreach ($files as $file) {
            $retrieveFile = $this->client->files()->retrieve($file);
            $assistantFile[] = $retrieveFile->toArray();
        }

        $assistant = $retrieveResponse->toArray();
        $assistantsList = $listResponse->toArray()['data'];
        // dd($assistantFile);

        return view('assistant', [
            'assistant' => $assistant,
            'assistants' => $assistantsList,
            'files' => $assistantFile,
        ]);
    }

    public function updateAssistant($assistantId, Request $request)
    {
        $existingAssistantFiles = $this->listAssistantFiles($assistantId);

        if ($request->hasFile('uploadFile')) {
            $newAssistantFile = $this->uploadFile($request->file('uploadFile'));

            $uploadFiles = array_merge($existingAssistantFiles, [$newAssistantFile['id']]);
            // Assuming you want to associate the uploaded file with the assistant
            $response = $this->client->assistants()->modify($assistantId, [
                'instructions' => $request->assistantInstruction,
                'name' => $request->assistantName,
                'file_ids' => $uploadFiles
            ]);

        } else {
            // If no file is uploaded, update assistant without file
            $response = $this->client->assistants()->modify($assistantId, [
                'instructions' => $request->assistantInstruction,
                'name' => $request->assistantName,
            ]);

            // Add logic to handle the API response or errors if needed
        }

        return redirect()->route('assistant');
    }

    public function listAssistantFiles($assistantId)
    {
        $response = $this->client->assistants()->files()->list($assistantId);

        $files = $response->toArray();
        // Extract file IDs from the response
        if (isset($files['data']) && !empty($files['data'])) {
            // Extract all file IDs from the response
            $fileIds = array_map(function ($file) {
                return $file['id'];
            }, $files['data']);

            return $fileIds;
        }

        return [];

    }

    public function uploadFile($file)
    {
        $fileName = $file->getClientOriginalName();
        $file->storeAs('uploads', $fileName, 'public');
        $fileContent = Storage::disk('public')->readStream("uploads/{$fileName}");

        $fileResponse = $this->client->files()->upload([
            'purpose' => 'assistants',
            'file' => $fileContent,
        ]);

        $assistantFile = $fileResponse->toArray();
        return $assistantFile;
    }

    public function deleteAssistant($assistantId)
    {
        $response = $this->client->assistants()->delete($assistantId);

        // return $response->toArray();
        return redirect()->route('assistant');
    }

    // public function createThread($assistantId)
    // {
    //     $response = $this->client->threads()->create([]);

    //     return redirect()->route('getThread', ['assistantId' => $assistantId, 'id' => $response->id]);
    // }

    public function getLastThread($assistantId) {
        $thread = Thread::where('assistant_id', $assistantId)
            ->orderBy('created_at', 'desc')
            ->value('thread_id');

        if ($thread) {
            return redirect()->route('getThread', ['assistantId' => $assistantId, 'id' => $thread]);
        } else {
            return redirect()->route('startChat', ['assistantId' => $assistantId]);
        }
    }
    
    public function createAndRunThread($assistantId)
    {
        $response = $this->client->threads()->createAndRun([
            'assistant_id' => $assistantId,
            'thread' => [
                'messages' =>
                    [
                        [
                            'role' => 'user',
                            'content' => 'Hello!',
                        ],
                    ],
            ],
        ],);

        Thread::create([
            'thread_id' => $response->threadId,
            'assistant_id' => $assistantId
        ]);

        return $response;
    }

    private function loadAnswer($threadRun)
    {
        while (in_array($threadRun->status, ['queued', 'in_progress'])) {
            $threadRun = $this->client->threads()->runs()->retrieve(
                threadId: $threadRun->threadId,
                runId: $threadRun->id,
            );
            sleep(1);
        }

        if ($threadRun->status !== 'completed') {
            $this->error = 'Request failed, please try again';
        }

        $messageList = $this->client->threads()->messages()->list(
            threadId: $threadRun->threadId,
        );
        
        $answer = $messageList->data[0]->content[0]->text->value;

        return $messageList;
    }

    public function startChat($assistantId) {
        $threadRun = $this->createAndRunThread($assistantId);

        $data = $this->loadAnswer($threadRun);
        $threadId = $data->data[0]->threadId;

        return redirect()->route('getThread', ['assistantId' => $assistantId, 'id' => $threadId]);
    }


    /**
     * Endpoint: /thread/{id}
     * Description: Retrieve thread info | id=threadId
     */
    public function getThread($assistantId, $threadId)
    {
        // get assistant response
        $response = $this->client->threads()->messages()->list($threadId);
        $messageList = $response->toArray();
        
        // retrieve assistant 
        $retrieveResponse = $this->client->assistants()->retrieve($assistantId);
        $assistantName = $retrieveResponse->name;

        // order messages and skip first message
        $sortedMessages = collect($messageList['data'])->sortBy('created_at')->values()->all();
        $data = array_slice($sortedMessages, 1);
        
        // Get thread list of an assistant
        $threads = Thread::where('assistant_id', $assistantId)->orderBy('created_at','desc')->get();

        // $data['data'] = collect($data['data'])->sortBy('created_at')->values()->all();
        return view("chatbot", compact('data', 'threadId', 'assistantId', 'assistantName', 'threads'));
    }

    public function deleteThread($assistantId,$threadId) {
        // delete thread from database
        Thread::where('thread_id', $threadId)->delete();
        
        // delete thread from assistant
        $this->client->threads()->delete($threadId);

        return redirect()->route('getLastThread', ['assistantId'=> $assistantId]);
    }
    /**
     * Endpoint: /thread/{id}/message
     * Description: Create Message in selected thread
     */
    public function createMessage($assistantId, $threadId, Request $request)
    {
        $assistantFiles = $this->listAssistantFiles($assistantId);

        $response = $this->client->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $request->message,
            'file_ids' => $assistantFiles
        ]);

        $message = $response->toArray();

        // Call OpenAI API to run thread
        $response = $this->client->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => $assistantId,
            ]
        );

        $data = $response->toArray();

        return response()->json(['message' => $message, 'data' => $data]);
    }


    /**
     * Endpoint: /thread/{threadId}/run
     * Description: List all Runs
     */
    public function listRuns($threadId, $runId)
    {

        // $client = new Client();
        // $url = 'https://api.openai.com/v1/threads/' . $threadId . '/runs/' . $runId . '/steps';

        // $response = $client->get($url, [
        //     'headers' => [
        //         'Authorization' => 'Bearer ' . $this->openaiApiKey,
        //         'OpenAI-Beta' => 'assistants=v1',
        //         'Content-Type' => 'application/json',
        //     ],
        // ]);

        // $thread = json_decode($response->getBody(), true);

        $response = $this->client->threads()->runs()->retrieve(
            threadId: $threadId,
            runId: $runId,
        );

        $thread = $response->toArray();

        return response()->json($thread);
    }

    public function retrieveMessage($threadId)
    {

        // $client = new Client();

        // $response = $client->get("https://api.openai.com/v1/threads/$threadId/messages", [
        //     'headers' => [
        //         'Content-Type' => 'application/json',
        //         'OpenAI-Beta' => 'assistants=v1',
        //         'Authorization' => 'Bearer ' . $this->openaiApiKey,
        //     ],
        // ]);

        // $data = json_decode($response->getBody(), true);
        $response = $this->client->threads()->messages()->list($threadId);

        $data = $response->toArray();

        return response()->json(['message' => $data]);
    }

    public function deleteFile($assistantId, $fileId)
    {
        $this->client->assistants()->files()->delete(
            assistantId: $assistantId,
            fileId: $fileId
        );

        return redirect()->route('retrieveAssistant', ['assistantId' => $assistantId]);
    }
}
