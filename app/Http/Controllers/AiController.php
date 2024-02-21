<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
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

        // $response = $this->client->assistants()->create([
        //     'instructions' => 'You are a personal math tutor. When asked a question, write and run Python code to answer the question.',
        //     'name' => 'New assistance',
        //     'tools' => [
        //         [
        //             'type' => 'code_interpreter',
        //         ],
        //     ],
        //     'model' => 'gpt-4',
        // ]);
        $response = $this->client->assistants()->create([
            'instructions' => $request->assistantInstruction,
            'name' => $request->assistantName,
            'tools' => [
                [
                    'type' => 'code_interpreter',
                ],
            ],
            'model' => 'gpt-4',
        ]);

        // $assistant = $response->toArray();
        return redirect()->route('assistant'); // ['id' => 'asst_VAGZ8DjGncGKfLCBojPPJXVU', ...]
    }

    public function listAssistants()
    {
        $response = $this->client->assistants()->list([
            'limit' => 10,
        ]);

        $assistant = $response->toArray();
        // return route('assistant', ['assistant'=> $assistant]);
        return view('assistant', ['assistants' => $assistant['data']]);
        // return response()->json($assistant);
    }

    public function retrieveAssistant($assistantId)
    {
        $retrieveResponse = $this->client->assistants()->retrieve($assistantId);
        $listResponse = $this->client->assistants()->list([
            'limit' => 10,
        ]);

        $assistant = $retrieveResponse->toArray();
        $assistantsList = $listResponse->toArray()['data'];

        return view('assistant', [
            'assistant' => $assistant,
            'assistants' => $assistantsList,
        ]);
    }

    public function updateAssistant($assistantId, Request $request) {
        $response = $this->client->assistants()->modify($assistantId, [
            'instructions' => $request->assistantInstruction,
            'name' => $request->assistantName,
        ]);
        
        return redirect()->route('assistant');
    }

    public function deleteAssistant($assistantId) {
        $response = $this->client->assistants()->delete($assistantId);

        // return $response->toArray();
        return redirect()->route('assistant');
    }

    public function createThread($assistantId)
    {
        $response = $this->client->threads()->create([]);

        return redirect()->route('getThread', ['assistantId' => $assistantId,'id'=>$response->id]);
        // return $response->toArray();
    }

    /**
     * Endpoint: /thread/{id}
     * Description: Retrieve thread info | id=threadId
     */
    public function getThread($assistantId,$threadId)
    {
        $response = $this->client->threads()->messages()->list($threadId);
        $retrieveResponse = $this->client->assistants()->retrieve($assistantId);

        $data = $response->toArray();
        $assistantName = $retrieveResponse->name;

        $data['data'] = collect($data['data'])->sortBy('created_at')->values()->all();
        return view("chatbot", compact('data', 'threadId', 'assistantId', 'assistantName'));
    }

    /**
     * Endpoint: /thread/{id}/message
     * Description: Create Message in selected thread
     */
    // public function createMessage(Request $request) {
    //     // threadId = thread_28YHDt2qejm6HYNdrxajEiad
    //     dd($request);

    //     $response = $this->client->threads()->messages()->create($request->threadId, [
    //         'role' => 'user',
    //         'content' => $request->message,
    //     ]);

    //     $message = $response->toArray();

    //     return response()->json(['message'=>$message]);
    //     // return redirect()->route('runThread', [
    //     //     'threadId' => $request->threadId,
    //     //     'assistantId' => 'asst_t8Yd7DWAghuHJwdDC2iAzb2E',
    //     // ])->with(['message' => $message]);
    //     // return redirect()->route('runThread')->with(['message'=>$message]); // msg_RHhrHijYdqkJXX5zONAMkOQH
    // }
    public function createMessage($assistantId,$threadId,Request $request)
    {
        // Call OpenAI API to create message
        $response = $this->client->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $request->message,
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

        // Return JSON response
        return response()->json(['message' => $message, 'data' => $data]);
    }


    /**
     * Endpoint: /thread/{threadId}/run
     * Description: List all Runs
     */
    public function listRuns($threadId, $runId)
    {

        $client = new Client();
        $url = 'https://api.openai.com/v1/threads/' . $threadId . '/runs/' . $runId;

        $response = $client->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'OpenAI-Beta' => 'assistants=v1',
                'Content-Type' => 'application/json',
            ],
        ]);

        $thread = json_decode($response->getBody(), true);

        return response()->json($thread);
    }

    public function retrieveMessage($threadId)
    {

        $client = new Client();

        $response = $client->get("https://api.openai.com/v1/threads/$threadId/messages", [
            'headers' => [
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v1',
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return response()->json(['message' => $data]);
    }

    /**
     * Endpoint: /run-thread/{threadId}/{assistantId}
     * Description: Run Thread
     */
    // public function runThread($threadId,$assistantId) {
    //     // assistantId = asst_t8Yd7DWAghuHJwdDC2iAzb2E

    //     $response = $this->client->threads()->runs()->create(
    //         threadId: $threadId, 
    //         parameters: [
    //             'assistant_id' => $assistantId,
    //         ],
    //     );

    //     $data = $response->toArray();

    //     return response()->json(['data'=>$data]);
    //     // return redirect()->route('getThread', ['id' => $threadId])->with(['data'=>$data]);
    //     // return response()->json($response->toArray()); // run_OhEtOQ77B8RdtxpDZcne9YRe
    // }

    /**
     * Endpoint: /submit-run/{threadId}/{runId}
     * Description: Submit Run
     */
    // public function submitRun($threadId,$runId)
    // {

    //     $response = $this->client->threads()->runs()->submitToolOutputs(
    //         threadId: $threadId,
    //         runId: $runId,
    //         parameters: [
    //             'tool_outputs' => [],
    //         ]
    //     );

    //     return $response->toArray();

    // }
    // public function submitRun($threadId,$runId)
    // {
    //     
    //     $client = new Client();
    //     $url = env('OPENAI_URL').'/threads/'.$threadId.'/runs/'.$runId.'/submit_tool_outputs';

    //     $response = $this->client->post($url, [
    //         'headers' => [
    //             'Authorization' => 'Bearer ' . $yourApiKey,
    //             'OpenAI-Beta' => 'assistants=v1',
    //             'Content-Type' => 'application/json',
    //         ],
    //         'json' => [
    //             'tool_outputs' => [],
    //         ]
    //     ]);

    //     $run = json_decode($response->getBody(), true);

    //     return response()->json($run);
    // }   
}
