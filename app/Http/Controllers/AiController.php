<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use OpenAI;

class AiController extends Controller
{
    private $openaiApiKey;

    public function __construct()
    {
        $this->openaiApiKey = env('OPENAI_API_KEY');
    }

    // public function index()
    // {
    //     $yourApiKey = $this->openaiApiKey;

    //     $client = OpenAI::client($yourApiKey);

    //     $result = $client->chat()->create([
    //         'model' => 'gpt-4',
    //         'messages' => [
    //             [
    //                 'role' => 'user',
    //                 'content' => 'You are openAI model?'
    //             ],
    //         ],
    //     ]);

    //     echo $result->choices[0]->message->content; // Hello! How can I assist you today?
    // }
    public function index()
    {
        return view("chatbot");
    }

    public function createAssistance()
    {
        $yourApiKey = $this->openaiApiKey;
        $client = OpenAI::client($yourApiKey);

        $response = $client->assistants()->create([
            'instructions' => 'You are a personal math tutor. When asked a question, write and run Python code to answer the question.',
            'name' => 'New assistance',
            'tools' => [
                [
                    'type' => 'code_interpreter',
                ],
            ],
            'model' => 'gpt-4',
        ]);

        // $response->id; // 'asst_gxzBkD1wkKEloYqZ410pT5pd'
        // $response->object; // 'assistant'
        // $response->createdAt; // 1623936000
        // $response->name; // 'Math Tutor'
        // $response->instructions; // 'You are a personal math tutor. When asked a question, write and run Python code to answer the question.'
        // $response->model; // 'gpt-4'
        // $response->description; // null
        // $response->tools[0]->type; // 'code_interpreter'
        // $response->fileIds; // []
        // $response->metadata; // []

        return $response->toArray(); // ['id' => 'asst_gxzBkD1wkKEloYqZ410pT5pd', ...]
    }

    public function listAssistants()
    {
        $yourApiKey = $this->openaiApiKey;
        $client = OpenAI::client($yourApiKey);

        $response = $client->assistants()->list([
            'limit' => 10,
        ]);

        return response()->json($response->toArray());
    }

    public function createThread()
    {
        $yourApiKey = $this->openaiApiKey;
        $client = OpenAI::client($yourApiKey);

        $response = $client->threads()->create([]);

        // $response->id; // 'thread_tKFLqzRN9n7MnyKKvc1Q7868'
        // $response->object; // 'thread'
        // $response->createdAt; // 1623936000
        // $response->metadata; // []
        // {"id":"thread_28YHDt2qejm6HYNdrxajEiad","object":"thread","created_at":1707461455,"metadata":[]}

        return $response->toArray();
    }

    /**
     * Endpoint: /thread/{id}
     * Description: Retrieve thread info | id=threadId
     */
    public function getThread($threadId) {
        $yourApiKey = $this->openaiApiKey;
        $client = OpenAI::client($yourApiKey);

        $response = $client->threads()->messages()->list($threadId);

        $data = $response->toArray();

        $data['data'] = collect($data['data'])->sortBy('created_at')->values()->all();
        return view("chatbot", compact('data', 'threadId'));

        // return redirect("chatbot")->with(['data' => $data]);
        // return view("home", compact($data)); //, ['data'=>$data]);
    }

    /**
     * Endpoint: /thread/{id}/message
     * Description: Create Message in selected thread
     */
    // public function createMessage(Request $request) {
    //     // threadId = thread_28YHDt2qejm6HYNdrxajEiad
    //     dd($request);
    //     $yourApiKey = $this->openaiApiKey;
    //     $client = OpenAI::client($yourApiKey);
        
    //     $response = $client->threads()->messages()->create($request->threadId, [
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
    public function createMessage(Request $request)
    {
        // dd($request->message);
        $yourApiKey = $this->openaiApiKey;
        $client = OpenAI::client($yourApiKey);

        // Call OpenAI API to create message
        $response = $client->threads()->messages()->create($request->threadId, [
            'role' => 'user',
            'content' => $request->message,
        ]);

        $message = $response->toArray();

        // Call OpenAI API to run thread
        $response = $client->threads()->runs()->create(
            threadId: $request->threadId,
            parameters: [
                'assistant_id' => 'asst_t8Yd7DWAghuHJwdDC2iAzb2E',
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
    public function listRuns($threadId,$runId) 
    {
        $yourApiKey = $this->openaiApiKey;
        $client = new Client();
        $url = 'https://api.openai.com/v1/threads/'.$threadId. '/runs/'.$runId;

        $response = $client->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $yourApiKey,
                'OpenAI-Beta' => 'assistants=v1',
                'Content-Type' => 'application/json',
            ],
        ]);

        $thread = json_decode($response->getBody(), true);

        return response()->json($thread);
    }
    
    public function retrieveMessage($threadId) {

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
    //     $yourApiKey = $this->openaiApiKey;
    //     $client = OpenAI::client($yourApiKey);
        
    //     $response = $client->threads()->runs()->create(
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
    //     $yourApiKey = $this->openaiApiKey;

    //     $client = OpenAI::client($yourApiKey);
    //     $response = $client->threads()->runs()->submitToolOutputs(
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
    //     $yourApiKey = $this->openaiApiKey;
    //     $client = new Client();
    //     $url = env('OPENAI_URL').'/threads/'.$threadId.'/runs/'.$runId.'/submit_tool_outputs';

    //     $response = $client->post($url, [
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
