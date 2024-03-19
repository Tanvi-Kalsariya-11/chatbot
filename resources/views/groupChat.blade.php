@extends('layout')
<title>Group-Chat</title>
@section('content')
    <link href="{{ asset('assets/css/chatbot.css') }}" rel="stylesheet" />
    <!-- Group Invitation Modal -->
    <div class="modal fade" id="invitationModel" tabindex="-1" aria-labelledby="invitationModelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="invitationModelLabel">Group Invitation</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>This is invitation request to join {{ $group['name'] }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-decline-invitation"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-accept-invitation">Join</button>
                </div>
            </div>
        </div>
    </div>
    <div class="page-content page-container" id="page-content">
        <div class="row">
            <div class="col-md-3 list-group">
                <a href="{{ route('listGroups') }}"
                    class="list-group-item list-group-item-action list-group-item-info fs-5">
                    Back to Groups List
                </a>
            </div>
            <div class="padding col-md-9">
                <div class="row container d-flex justify-content-center">
                    <div class="col-md-12">

                        <div class="box box-warning direct-chat direct-chat-warning">
                            <div class="box-header with-border d-flex justify-content-between">
                                <h3 class="box-title">{{ $group->name }}</h3>
                                <a href="#" class="fs-5 text-primary fw-bold text-decoration-none"
                                    data-bs-toggle="modal" data-bs-target="#shareModal">
                                    <i class="fa-solid fa-share-nodes"></i>
                                </a>
                                {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    Launch demo modal
                                  </button> --}}
                                <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="shareModalLabel">Copy group link</h1>
                                                {{-- <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button> --}}
                                            </div>
                                            <div class="modal-body">
                                                <p id="routeContainer">{{ route('groupChat', ['groupId' => $group['id']]) }} </p>
                                                    {{-- <i
                                                        id="copyIcon" class="fa-regular fa-copy"
                                                        style="cursor: pointer;"></i></p> --}}
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="box-body">
                                <div class="direct-chat-messages" id="chatMessages">
                                    @if (isset($messages))
                                        @foreach ($messages as $message)
                                            <div class="direct-chat-msg {{ $message['role'] == 'user' ? 'right' : '' }}">
                                                <div class="direct-chat-info clearfix">
                                                    <span class="direct-chat-name pull-left">{{ $message['role'] == 'user' ? $message->user->name : 'Assistant' }}</span>
                                                </div>
                                                <img class="direct-chat-img" src="{{ $message['role'] == 'user' ? 'https://img.icons8.com/office/36/000000/person-female.png' : 'https://img.icons8.com/color/36/000000/administrator-male.png' }}" alt="message user image">
                                                <div class="direct-chat-text">
                                                    @if ($message['role'] == 'assistant')
                                                        <pre>{{ $message['message'] }}</pre>
                                                    @else
                                                        <p>{{ $message['message'] }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p>Select Thread</p>
                                    @endif
                                    <div class="direct-chat-msg" id="typingLoaderBlock" style="display: none;">
                                        <div class="direct-chat-info clearfix">
                                            <span class="direct-chat-name pull-left">Assistant</span>
                                        </div>
                                        <img class="direct-chat-img" src="https://img.icons8.com/color/36/000000/administrator-male.png" alt="message user image">
                                        <div class="direct-chat-text bg-loader">
                                            <pre id="typingLoader" class="loading medium">            </div>
                                    </div>
                                </div>
                            </div>

                            <div class="box-footer">
                                <form id="messageForm" action="{{ route('sendMessage', ['groupId' => $group['id']]) }}"
                                    method="post">
                                    @csrf
                                    {{-- <div class="mention-dropdown" id="mentionDropdown"></div> --}}
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

@endsection
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
{{-- <script src="{{ asset('js/app.js') }}"></script> --}}
@vite('resources/js/app.js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let chatMessages = document.getElementById('chatMessages');
        let messageInput = document.getElementById('messageInput');
        let sendMessageBtn = document.getElementById('sendMessageBtn');
        let messageForm = document.getElementById('messageForm');
       
        chatMessages.scrollTop = chatMessages.scrollHeight;
        messageInput.focus();
       
        let groupId = {{$group['id']}};
        Echo.private(`group.${groupId}`)
        .listen('NewGroupMessage', (e) => {
            let message = e.message;
            appendMessage(message);
        });
        
        messageForm.addEventListener('submit', function (event) {
            event.preventDefault();
            sendMessage();
        });
        
        sendMessageBtn.addEventListener('click', function () {
            sendMessage();
        });
        
        function sendMessage() {
            let message = messageInput.value.trim();
            if (message !== '') {
                axios.post(messageForm.action, {
                    message: message
                })
                .then(function (response) {
                    messageInput.value = '';
                })
                .catch(function (error) {
                    console.error(error);
                });
            }
        }
        
        function appendMessage(message) {
            let messageElement = document.createElement('div');
            messageElement.classList.add('direct-chat-msg');
            if (message.role === 'user') {
                messageElement.classList.add('right');
            }
            messageElement.innerHTML = `
                <div class="direct-chat-info clearfix">
                    <span class="direct-chat-name pull-left">${message.role === 'user' ? message.user.name : 'Assistant'}</span>
                </div>
                <img class="direct-chat-img" src="${message.role === 'user' ? 'https://img.icons8.com/office/36/000000/person-female.png' : 'https://img.icons8.com/color/36/000000/administrator-male.png'}" alt="message user image">
                <div class="direct-chat-text">
                    ${message.role === 'assistant' ? '<pre>' + message.message + '</pre>' : '<p>' + message.message + '</p>'}
                </div>
            `;
            chatMessages.appendChild(messageElement);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            messageInput.value = '';
        }
    });
</script>

{{-- <script src="{{ asset('assets/js/groupChat.js') }}"></script> --}}

{{-- <script>
    document.addEventListener("DOMContentLoaded", function() {
        const chatMessages = document.getElementById("chatMessages");
        const messageInput = document.getElementById("messageInput");
        const sendMessageBtn = document.getElementById("sendMessageBtn");

        chatMessages.scrollTop = chatMessages.scrollHeight;
        messageInput.focus();

        function updateChat() {
            // Fetch new messages based on the groupId
            $.ajax({
                url: "{{ route('groupChat', ['groupId' => $group['id']]) }}",
                method: "GET",
                success: function(data) {
                    appendNewMessages(data);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                },
                complete: function() {
                    // After updating the chat, call updateChat() again
                    setTimeout(updateChat, 3000); // Fetch new messages every 5 seconds
                }
            });
        }

        function appendNewMessages(data) {
            const newMessagesContainer = document.createElement('div');
            newMessagesContainer.innerHTML = data;

            // Get all new messages
            const newMessages = newMessagesContainer.querySelectorAll('.direct-chat-msg');

            // Remove all existing messages
            chatMessages.innerHTML = '';

            // Append each new message in ascending order
            newMessages.forEach(newMessage => {
                // Find the correct position to insert the new message
                let insertIndex = 0;
                const existingMessages = chatMessages.querySelectorAll('.direct-chat-msg');
                for (let i = 0; i < existingMessages.length; i++) {
                    const messageTimestamp = new Date(newMessage.dataset.timestamp).getTime();
                    const existingMessageTimestamp = new Date(existingMessages[i].dataset.timestamp)
                        .getTime();

                    if (messageTimestamp > existingMessageTimestamp) {
                        break;
                    }
                    insertIndex++;
                }

                // Insert the new message at the correct position
                if (insertIndex === existingMessages.length) {
                    chatMessages.appendChild(newMessage);
                } else {
                    chatMessages.insertBefore(newMessage, existingMessages[insertIndex]);
                }
            });

            // Scroll to the bottom of the conversation to show the latest message
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Call updateChat on page load
        $(document).ready(function() {
            updateChat();
        });

        // Function to submit the message form using AJAX
        $('#sendMessageBtn').on('click', function() {
            $.ajax({
                url: $("#messageForm").attr("action"),
                method: $("#messageForm").attr("method"),
                data: $("#messageForm").serialize(),
                success: function() {
                    // Clear the input field after successful submission
                    $('#messageInput').val('');
                    // Don't fetch new messages immediately after sending a message, let the periodic polling handle it
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script> --}}

<script>
    // var groupId = {{ $group->id ?? null }};
    // Show Invitation group popup to new users
    $(document).ready(function() {
        // Check if the user is attached to the group
        @if (Auth::user() && !Auth::user()->groups->contains('id', $groupId))
            // Show the invitation modal if the user is not attached to the group
            $('#invitationModel').modal('show');

            // Handle Accept button click
            $('.btn-accept-invitation').on('click', function() {
                window.location.href = '{{ route('acceptGroupInvite', ['groupId' => $groupId]) }}';
            });

            // Handle Decline button click
            $('.btn-decline-invitation').on('click', function() {
                window.location.href = '{{ route('listGroups') }}';
            });
        @endif
    });
</script>

{{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Check if ClipboardJS is available
      if (typeof ClipboardJS !== 'undefined') {
        // Initialize Clipboard.js with the ID of the copy icon
        new ClipboardJS('#copyIcon', {
          text: function() {
            // Function to return the text to be copied (in this case, the route)
            return document.getElementById('routeContainer').innerText.trim();
          }
        });
  
        // Show a tooltip or any other indication when the copy is successful
        document.getElementById('copyIcon').addEventListener('click', function() {
          alert('Route copied to clipboard!');
        });
      } else {
        console.error('ClipboardJS library not found. Make sure it is properly included.');
      }
    });
  </script> --}}