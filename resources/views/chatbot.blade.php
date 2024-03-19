{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href=" {{ asset('assets/css/bootstrap.min.css') }} " rel="stylesheet" />
    <link href=" {{ asset('assets/js/bootstrap.bundle.min.js') }} " rel="stylesheet" />
    <link href=" {{ asset('assets/js/jquery.min.js') }} " rel="stylesheet" />
    <link href=" {{ asset('assets/css/font-awesome.css') }} " rel="stylesheet" /> --}}

@extends('layout')
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Chatbot</title>


{{-- <style></style>
    </head>
    
    <body> --}}
@section('content')
    <link href="{{ asset('assets/css/chatbot.css') }}" rel="stylesheet" />
    {{-- <h1>Chatbot View</h1> --}}

    <!-- Generate a URL for the getThread route with the id parameter -->
    {{-- <h4><a href="{{ route('getThread', ['id' => 'thread_28YHDt2qejm6HYNdrxajEiad']) }}">Open Thread</a></h4> --}}
    {{-- <h4><a href=" {{ route('assistant') }} ">Assistant</a></h4> --}}
    <div class="page-content page-container" id="page-content">
        <div class="row">
            <div class="col-md-3 list-group">
                <a href="{{ route('listUserAssistants') }}"
                    class="list-group-item list-group-item-action list-group-item-info fs-5">
                    Back to Assistant List
                </a>
                @if (isset($threads))
                    @foreach ($threads as $thread)
                        <div
                            class="list-group-item d-flex list-group-item-action justify-content-between align-items-center {{ isset($threadId) && $threadId == $thread->thread_id ? 'list-group-item-dark' : '' }}">
                            <a href="{{ route('getThread', ['assistantId' => $thread->assistant_id, 'id' => $thread->thread_id]) }}"
                                class="text-decoration-none">
                                {{ $thread->thread_id }}
                            </a>
                            <span class="badge text-danger text-decoration-none"><a
                                    href="{{ route('deleteThread', ['assistantId' => $thread->assistant_id, 'threadId' => $thread->thread_id]) }}"><i
                                        class="fa-solid fa-trash-can"></i></a></span>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="padding col-md-9">
                <div class="row container d-flex justify-content-center">
                    <div class="col-md-12">

                        <div class="box box-warning direct-chat direct-chat-warning">
                            <div class="box-header with-border d-flex justify-content-between">
                                <h3 class="box-title">{{ $assistantName }}</h3>
                                <a href="{{ route('startChat', ['assistantId' => $assistantId]) }}"
                                    class="fs-5 text-success fw-bold text-decoration-none">
                                    <i class="fa-solid fa-pen-to-square"></i> New Chat
                                </a>
                            </div>

                            <div class="box-body">

                                <div class="direct-chat-messages" id="chatMessages">
                                    @if (isset($data))
                                        @foreach ($data as $message)
                                            <div class="direct-chat-msg">
                                                @if ($message['role'] == 'assistant' && isset($message['content'][0]['text']['value']))
                                                    <div class="direct-chat-info clearfix">
                                                        <span class="direct-chat-name pull-left">Assistant</span>
                                                    </div>

                                                    <img class="direct-chat-img"
                                                        src="https://img.icons8.com/color/36/000000/administrator-male.png"
                                                        alt="message user image">

                                                    <div class="direct-chat-text">
                                                        <pre>{{ $message['content'][0]['text']['value'] }}</pre>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="direct-chat-msg right">
                                                @if ($message['role'] == 'user' && isset($message['content'][0]['text']['value']))
                                                    <div class="direct-chat-info clearfix">
                                                        <span class="direct-chat-name pull-left">User</span>
                                                    </div>

                                                    <img class="direct-chat-img"
                                                        src="https://img.icons8.com/office/36/000000/person-female.png"
                                                        alt="message user image">

                                                    <div class="direct-chat-text">
                                                        <p>{{ $message['content'][0]['text']['value'] }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <p>Select Thread</p>
                                    @endif
                                    <div class="direct-chat-msg" id="typingLoaderBlock" style="display: none;">
                                        <div class="direct-chat-info clearfix">
                                            <span class="direct-chat-name pull-left">Assistant</span>
                                        </div>

                                        <img class="direct-chat-img"
                                            src="https://img.icons8.com/color/36/000000/administrator-male.png"
                                            alt="message user image">

                                        <div class="direct-chat-text bg-loader">
                                            <pre id="typingLoader" class="loading medium"><span>.</span><span>.</span><span>.</span></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="box-footer">
                                <form id="messageForm"
                                    action="{{ route('createMessage', ['assistantId' => $assistantId, 'threadId' => $threadId]) }}"
                                    method="post">
                                    @csrf
                                    <div class="input-group">
                                        <input id="messageInput" type="text" name="message"
                                            placeholder="Type Message ..." class="form-control" autocomplete="off">
                                        <span class="input-group-btn">
                                            <button id="sendMessageBtn" type="button"
                                                class="btn btn-warning btn-flat">Send</button>
                                        </span>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <script src="{{ asset('assets/js/chatbot.js') }}"></script> --}}
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        const chatMessages = document.getElementById("chatMessages");
    
        chatMessages.scrollTop = chatMessages.scrollHeight;
        messageInput.focus();
    
        document.getElementById('messageForm').addEventListener('submit', function(event) {
            event.preventDefault();
            sendMessage();
        });
    
        document.getElementById('sendMessageBtn').addEventListener('click', function() {
            sendMessage();
        });
    
        function displayTypingLoader(show) {
            typingLoaderBlock.style.display = show ? "block" : "none";

            // If showing, append it to the end of the chat
            if (show) {
                chatMessages.appendChild(typingLoaderBlock);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }

        function sendMessage() {
            var messageInput = document.getElementById('messageInput');
            var message = messageInput.value;
            var chatMessages = document.getElementById('chatMessages');
            // Clear the message input
            messageInput.value = '';
            // Send the user message to the backend
            fetch('{{ route('createMessage', ['assistantId' => $assistantId, 'threadId' => $threadId]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    message: message
                })
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                // Handle the response and update the chat messages
                var userMessage = data.message;
                // Append the user message to the chat messages
                var userMessageHtml = `
                <div class="direct-chat-msg right">
                    <div class="direct-chat-info clearfix">
                        <span class="direct-chat-name pull-left">User</span>
                    </div>
                    <img class="direct-chat-img" src="https://img.icons8.com/office/36/000000/person-female.png" alt="message user image">
                    <div class="direct-chat-text">
                        <p>${userMessage.content[0].text.value}</p>
                    </div>
                </div>
                `;
                chatMessages.innerHTML += userMessageHtml;
                // Initiate the streaming response after appending user message
                initiateStreamingResponse();
                chatMessages.scrollTop = chatMessages.scrollHeight;
            })
            .catch(function(error) {
                console.error('Error:', error);
            });
        }
    
        function initiateStreamingResponse() {
            // Append the assistant's response block to the chat messages
            var assistantResponseHtml = `
            <div class="direct-chat-msg">
                <div class="direct-chat-info clearfix">
                    <span class="direct-chat-name pull-left">Assistant</span>
                </div>
                <img class="direct-chat-img" src="https://img.icons8.com/color/36/000000/administrator-male.png" alt="message user image">
                <div class="direct-chat-text">
                    <pre class="streamedResponse"></pre>
                </div>
            </div>
            `;
            chatMessages.innerHTML += assistantResponseHtml;
            // Initiate the streaming response
            var streamedResponse = document.querySelector('.direct-chat-msg:last-child .streamedResponse');
            var eventSource = new EventSource(
                '{{ route('streamResponse', ['threadId' => $threadId, 'assistantId' => $assistantId]) }}');
            eventSource.onmessage = function(event) {
                var data = event.data;
                if (data === '[DONE]') {
                    eventSource.close();
                } else {
                    streamedResponse.innerHTML += data;
                    streamedResponse.scrollTop = streamedResponse.scrollHeight;
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            };
            eventSource.onerror = function(event) {
                console.error('EventSource error:', event);
                streamedResponse.innerHTML += '<p class="error">An error occurred. Please try again.</p>';
                eventSource.close();
            };
        }
    </script>
@endsection
