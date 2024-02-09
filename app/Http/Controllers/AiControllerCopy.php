<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use OpenAI;

class AiController extends Controller
{
    // private $openaiApiKey = env('OPENAI_API_KEY');
    // private $openaiBetaHeader = $factory->withHttpHeader('OpenAI-Beta', 'assistants=v1');

    public function index()
    {
        $yourApiKey = env('OPENAI_API_KEY');

        $client = OpenAI::client($yourApiKey);

        $result = $client->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'You are openAI model?'
                ],
            ],
        ]);

        echo $result->choices[0]->message->content; // Hello! How can I assist you today?
    }

    public function createAssistance()
    {
        $yourApiKey = env('OPENAI_API_KEY');
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
        $yourApiKey = env('OPENAI_API_KEY');
        $client = OpenAI::client($yourApiKey);

        $response = $client->assistants()->list([
            'limit' => 10,
        ]);

        return response()->json($response->toArray());
    }
    // public function listAssistants()
    // {
    //     $openaiApiKey = env('OPENAI_API_KEY');
    //     $client = new Client();
    //     $url = env('OPENAI_URL').'/assistants';

    //     $response = $client->get($url, [
    //         'headers' => [
    //             'Authorization' => 'Bearer ' . $openaiApiKey,
    //             'OpenAI-Beta' => 'assistants=v1',
    //             'Content-Type' => 'application/json',
    //         ],
    //         'query' => [
    //             'order' => 'desc',
    //             'limit' => 20,
    //         ],
    //     ]);

    //     $assistants = json_decode($response->getBody(), true);

    //     return response()->json($assistants['data']);
    // }

    public function createThread()
    {
        $yourApiKey = env('OPENAI_API_KEY');
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
    // public function getThread($threadId)
    // {
    //     $openaiApiKey = env('OPENAI_API_KEY');
    //     $client = new Client();
    //     $url = env('OPENAI_URL').'/threads/'.$threadId;

    //     $response = $client->get($url, [
    //         'headers' => [
    //             'Authorization' => 'Bearer ' . $openaiApiKey,
    //             'OpenAI-Beta' => 'assistants=v1',
    //             'Content-Type' => 'application/json',
    //         ],
    //     ]);

    //     $thread = json_decode($response->getBody(), true);

    //     return response()->json($thread);
    // }
    public function getThread($threadId) {
        $yourApiKey = env('OPENAI_API_KEY');
        $client = OpenAI::client($yourApiKey);

        $response = $client->threads()->messages()->list($threadId);

        return response()->json($response->toArray());
    }



    /**
     * Endpoint: /thread/{id}/message
     * Description: Create Message in selected thread
     */
    // public function createMessage($threadId) {
    //     // threadId = thread_28YHDt2qejm6HYNdrxajEiad
    //     $openaiApiKey = env('OPENAI_API_KEY');
    //     $client = new Client();
    //     $url = env('OPENAI_URL').'/threads/'.$threadId. '/messages';

    //     $response = $client->post($url, [
    //         'headers' => [
    //             'Authorization' => 'Bearer ' . $openaiApiKey,
    //             'OpenAI-Beta' => 'assistants=v1',
    //             'Content-Type' => 'application/json',
    //         ],
    //         'json' => [
    //             'role' => 'user',
    //             'content' => 'What is AI?'
    //         ]
    //     ]);

    //     $threadMessage = json_decode($response->getBody(), true);

    //     return response()->json($threadMessage); // msg_RHhrHijYdqkJXX5zONAMkOQH
    // }

    /**
     * Endpoint: /run-thread/{threadId}/{assistantId}
     * Description: Run Thread
     */
    public function runThread($threadId,$assistantId) {
        // assistantId = asst_t8Yd7DWAghuHJwdDC2iAzb2E
        $openaiApiKey = env('OPENAI_API_KEY');
        $client = new Client();
        $url = env('OPENAI_URL').'/threads/'.$threadId. '/runs';

        $response = $client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $openaiApiKey,
                'OpenAI-Beta' => 'assistants=v1',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'assistant_id' => $assistantId, // $request->assistantId
            ]
        ]);

        $thread = json_decode($response->getBody(), true);

        return response()->json($thread); // run_OhEtOQ77B8RdtxpDZcne9YRe

        // Output: 
        // {
        //     "id": "run_SkV5fgpRb7H10AnvsSlZ4GQX",
        //     "object": "thread.run",
        //     "created_at": 1707469877,
        //     "assistant_id": "asst_t8Yd7DWAghuHJwdDC2iAzb2E",
        //     "thread_id": "thread_28YHDt2qejm6HYNdrxajEiad",
        //     "status": "queued",
        //     "started_at": null,
        //     "expires_at": 1707470477,
        //     "cancelled_at": null,
        //     "failed_at": null,
        //     "completed_at": null,
        //     "last_error": null,
        //     "model": "gpt-4",
        //     "instructions": "You are a personal math tutor. When asked a question, write and run Python code to answer the question.",
        //     "tools": [
        //         {
        //             "type": "code_interpreter"
        //         }
        //     ],
        //     "file_ids": [],
        //     "metadata": [],
        //     "usage": null
        // }
    }

    /**
     * Endpoint: /submit-run/{threadId}/{runId}
     * Description: Submit Run
     */
    public function submitRun($threadId,$runId)
    {
        $yourApiKey = env('OPENAI_API_KEY');

        $client = OpenAI::client($yourApiKey);
        $response = $client->threads()->runs()->submitToolOutputs(
            threadId: $threadId,
            runId: $runId,
            parameters: [
                'tool_outputs' => [],
            ]
        );

        return $response->toArray();
        
    }
    // public function submitRun($threadId,$runId)
    // {
    //     $openaiApiKey = env('OPENAI_API_KEY');
    //     $client = new Client();
    //     $url = env('OPENAI_URL').'/threads/'.$threadId.'/runs/'.$runId.'/submit_tool_outputs';

    //     $response = $client->post($url, [
    //         'headers' => [
    //             'Authorization' => 'Bearer ' . $openaiApiKey,
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

    /**
     * Endpoint: /thread/{threadId}/run
     * Description: List all Runs
     */
    public function listRuns($threadId) {
        $openaiApiKey = env('OPENAI_API_KEY');
        $client = new Client();
        $url = env('OPENAI_URL').'/threads/'.$threadId. '/runs';

        $response = $client->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $openaiApiKey,
                'OpenAI-Beta' => 'assistants=v1',
                'Content-Type' => 'application/json',
            ],
        ]);

        $thread = json_decode($response->getBody(), true);

        return response()->json($thread);
    }   
}
