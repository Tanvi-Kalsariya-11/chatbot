<?php

namespace App\Http\Controllers;

use App\Events\groupChatWebSocket;
use App\Events\NewGroupMessage;
use App\Http\Controllers\Controller;
use App\Models\Assistants;
use App\Models\Group;
use App\Models\Messages;
use App\Models\Thread;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenAI;
use Str;

class GroupChatController extends Controller
{
    private $openaiApiKey;
    private $client;

    public function __construct()
    {
        $this->openaiApiKey = env('OPENAI_API_KEY');

        $yourApiKey = $this->openaiApiKey;
        $this->client = OpenAI::client($yourApiKey);
    }

    /**
     * Description:Get default assistant and list of all assistant created by authenticated user
     */
    public function selectAssistant()
    {
        $user = Auth::user();
        $userAssistants = Assistants::where('user_id', $user->id)->orWhere('default', 1)->get();

        $assistant = [];

        foreach ($userAssistants as $assistants) {
            $response = $this->client->assistants()->retrieve($assistants->assistant_id);

            $assistantDetails = $response->toArray();
            $assistantDetails['assistantId'] = $assistants->id;
            $assistant[] = $assistantDetails;
        }
        return response()->json($assistant);
    }

    /**
     * Description: Create and save new group
     */
    public function save(Request $request)
    {
        $user = Auth::user()->id;
        // dd($user);
        $group = Group::create([
            'name' => $request->groupName,
            'user_id' => $user,
            'assistant_id' => $request->selectedAssistant
        ]);

        Auth::user()->groups()->attach($group->id);

        return redirect()->back();
    }

    /**
     * Description: Retrieve group information for edit group
     */
    public function getGroup($groupId)
    {
        $group = Group::where('id', $groupId)->first();
        $userGroups = Auth::user()->groups()->orderByDesc('created_at')->get();

        // return $userGroups;
        return view('group', [
            'groups' => $userGroups,
            'group' => $group
        ]);
    }

    /**
     * Description: Edit and update Group information
     */
    public function update(Request $request, $groupId)
    {

        // $token = Str::random(32); // Generate a unique token

        // $invitationLink = route('groups.invite', ['token' => $token]);

        // $group = Group::where('id',$groupId);
    }

    /**
     * Description: List all the groups user is connected with
     */
    public function listGroups()
    {
        $userGroups = Auth::user()->groups()->orderByDesc('created_at')->get();

        return view('group', ['groups' => $userGroups]);
    }

    /**
     * Description: Open chat group to chat
     */
    public function groupChat($groupId)
    {
        $group = Group::where('id', $groupId)->first();
        $messages = Messages::with('user')->where('group_id', $groupId)->get();

        return view('groupChat', compact('group', 'groupId', 'messages'));
    }

    /**
     * Description: Manage user invitation for group
     */
    public function acceptGroupInvite($groupId)
    {
        $group = Group::where('id', $groupId)->first();
        Auth::user()->groups()->attach($groupId);

        return view('groupChat', compact('group'));
    }


    public function retrieveGroupChat($groupId)
    {
        // Retrieve new messages since lastMessageId for the specified group
        $group = Group::findOrFail($groupId);

        $messages = Messages::where('group_id', $groupId)
            ->orderBy('created_at')
            ->get();

        return view('groupChat', compact('messages'));
    }

    public function latestTimestamp($groupId)
    {
        $latestTimestamp = Messages::where('group_id', $groupId)->max('created_at');
    
        return response()->json(['latestTimestamp' => $latestTimestamp]);
    }

    /**
     * Description: Send message in chat group
     */
    public function sendMessage(Request $request, $groupId)
    {

        $request->validate([
            'message' => 'required'
        ]);

        // $user = Auth::user()->id;
        $isAssistantMessage = preg_match('/@ai|@AI|@Ai/', $request->message);
        $group = Group::findOrFail($groupId);
        $user = User::findOrFail(Auth::user()->id);

        $groupUsersCount = $group->users()->count();

        $messageInfo = [
            'thread_id' => NULL,
            'group_id' => $groupId,
            'user_id' => $user->id,
            'message' => $request->message,
            'role' => 'user'
        ];

        $message = Messages::create($messageInfo);
        broadcast(new NewGroupMessage([
            'id' => $message->id,
            'role' => 'user',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                // Include other relevant user data
            ],
            'message' => $message->message,
            // Include other relevant message data
        ], $groupId));

        // broadcast(new NewGroupMessage($message))->toOthers();
        if ($groupUsersCount > 1 && $isAssistantMessage) {
            $aiMessageInfo = $this->sendMessageToAi($request->message,$groupId);
            $aiMessage = Messages::create($aiMessageInfo);

            broadcast(new NewGroupMessage([
                'id' => $aiMessage->id,
                'role' => 'assistant',
                'message' => $aiMessage->message,
                // Include other relevant message data
            ], $groupId));
            // broadcast(new NewGroupMessage($aiMessage))->toOthers();
        } elseif ($groupUsersCount == 1) {
            $aiMessageInfo = $this->sendMessageToAi($request->message,$groupId);
            $aiMessage = Messages::create($aiMessageInfo);

            broadcast(new NewGroupMessage([
                'id' => $aiMessage->id,
                'role' => 'assistant',
                'message' => $aiMessage->message,
                // Include other relevant message data
            ], $groupId));
        }

        return response()->json(['message' => [
            'id' => $message->id,
            'role' => 'user',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'message' => $message->message,
        ]]);
        // return redirect()->route('groupChat', ['groupId' => $groupId]);
    }

    public function sendMessageToAi($message,$groupId)
    {
        $lastThread = Messages::where('group_id', $groupId)
            ->whereNotNull('thread_id')
            ->orderByDesc('created_at')
            ->value('thread_id');
        $group = Group::findOrFail($groupId);
        $assistant = Assistants::findOrFail($group->assistant_id);

        if ($lastThread) {
            $thread = Thread::findOrFail($lastThread);
            $aiResponse = $this->createMessageForGroupThread($assistant, $thread->thread_id, $message);
            $aiMessage = $aiResponse->data[0]->content[0]->text->value;

            $aiMessageInfo = [
                'thread_id' => $thread->id,
                'group_id' => $groupId,
                'user_id' => NULL,
                'message' => $aiMessage,
                'role' => 'assistant'
            ];

            return $aiMessageInfo;
        } else {
            $aiResponse = $this->startGroupChatThread($assistant, $message, $groupId);
            $aiMessage = $aiResponse->data[0]->content[0]->text->value;
            $threadId = $aiResponse->data[0]->threadId;

            $thread = Thread::where('thread_id', $threadId)->first();

            $aiMessageInfo = [
                'thread_id' => $thread->id,
                'group_id' => $groupId,
                'user_id' => NULL,
                'message' => $aiMessage,
                'role' => 'assistant'
            ];

            return $aiMessageInfo;
        }
    }

    public function createAndRunThread($assistant, $message, $groupId)
    {
        // $messageContent = preg_replace('/@ai|@AI|@Ai/', '', $message);

        // // Trim any leading or trailing spaces
        // $messageContent = trim($messageContent);

        $response = $this->client->threads()->createAndRun([
            'assistant_id' => $assistant->assistant_id,
            'thread' => [
                'messages' =>
                    [
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],
            ],
        ], );

        Thread::create([
            'ass_id' => $assistant->id,
            'group_id' => $groupId ?? NULL,
            'thread_id' => $response->threadId,
            'assistant_id' => $assistant->assistant_id
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

    public function startGroupChatThread($assistant, $message, $groupId)
    {
        $threadRun = $this->createAndRunThread($assistant, $message, $groupId);

        $data = $this->loadAnswer($threadRun);

        return $data;
    }

    public function createMessageForGroupThread($assistant, $threadId, $message)
    {
        $assistantFiles = $this->listAssistantFiles($assistant->assistant_id);

        $response = $this->client->threads()->messages()->create($threadId, [
            'role' => 'user',
            'content' => $message,
        ]);
        // 'file_ids' => $assistantFiles

        $message = $response->toArray();

        // Call OpenAI API to run thread
        $threadRun = $this->client->threads()->runs()->create(
            threadId: $threadId,
            parameters: [
                'assistant_id' => $assistant->assistant_id,
            ]
        );

        $data = $this->loadAnswer($threadRun);
        return $data;
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
    
    public function websocket() {
        event(new groupChatWebSocket);
    }

}
