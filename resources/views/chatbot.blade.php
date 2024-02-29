<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href=" {{ asset('assets/css/bootstrap.min.css') }} " rel="stylesheet" />
    <link href=" {{ asset('assets/js/bootstrap.bundle.min.js') }} " rel="stylesheet" />
    <link href=" {{ asset('assets/js/jquery.min.js') }} " rel="stylesheet" />
    <link href=" {{ asset('assets/css/font-awesome.css') }} " rel="stylesheet" />

    <link href="{{ asset('assets/css/chatbot.css') }}" rel="stylesheet" />

    <title>Chatbot</title>

    <style></style>
</head>

<body>
    {{-- <h1>Chatbot View</h1> --}}

    <!-- Generate a URL for the getThread route with the id parameter -->
    {{-- <h4><a href="{{ route('getThread', ['id' => 'thread_28YHDt2qejm6HYNdrxajEiad']) }}">Open Thread</a></h4> --}}
    {{-- <h4><a href=" {{ route('assistant') }} ">Assistant</a></h4> --}}
    <div class="page-content page-container" id="page-content">
        <div class="padding">
            <div class="row container d-flex justify-content-center">
                <div class="col-md-12">

                    <div class="box box-warning direct-chat direct-chat-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">{{$assistantName}}</h3>
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
                                        {{-- <div id="typingLoader">Typing...</div> --}}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <form id="messageForm" action="{{ route('createMessage', ['assistantId'=>$assistantId,'threadId'=>$threadId]) }}" method="post">
                                @csrf
                                {{-- <input type="hidden" name="threadId" value="thread_28YHDt2qejm6HYNdrxajEiad"> --}}
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

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="{{ asset('assets/js/chatbot.js') }}"></script>

</body>

</html>
