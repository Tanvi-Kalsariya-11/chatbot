// public/js/chat.js

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("messageForm");
    const messageInput = document.getElementById("messageInput");
    const sendMessageBtn = document.getElementById("sendMessageBtn");
    const typingLoaderBlock = document.getElementById("typingLoaderBlock");
    const chatMessages = document.getElementById("chatMessages");

    chatMessages.scrollTop = chatMessages.scrollHeight;
    messageInput.focus();

    function sendMessage() {
        const message = messageInput.value;

        // Display user message immediately
        displayMessage("User", message, false, true);

        // Clear the input
        messageInput.value = "";

        // Make AJAX request to createMessage
        $.ajax({
            type: "POST",
            url: form.action,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
            },
            data: {
                message: message,
                threadId: form.threadId.value,
            },
            success: function (data) {
                // Check run status before retrieving last message
                const runId = data.data.id;
                
                // Show typing loader
                displayTypingLoader(true);

                checkRunStatus(runId,data);
            },
            error: function (error) {
                console.error("Error:", error);
            },
        });
    }

    function checkRunStatus(runId,data) {
        $.ajax({
            type: "GET",
            url: `/thread/${form.threadId.value}/run/${runId}`,
            success: function (runStatus) {
                if (runStatus && runStatus.completed_at) {
    
                    // Make AJAX request to retrieve last message
                    $.ajax({
                        type: "GET",
                        url: `/retrieve-message/${form.threadId.value}`,
                        success: function (responseData) {
                            assistantResponse = responseData.message.data[0].content[0].text.value;

                            // Hide typing loader
                            // typingLoaderBlock.style.display = "none";
                            displayTypingLoader(false);
                            
                            // Check if the last message is from the assistant
                            if (responseData.message && responseData.message.data[0].role === 'assistant') {
                                displayMessage("Assistant", assistantResponse, true, false);
                                // Update UI with the last message
                                // $('#chatMessages').append(assistantResponse);
                            }
                        },
                        error: function (error) {
                            console.error("Error:", error);
                        },
                    });
                } else {
                    // If run status is not completed, continue checking
                    // setTimeout(function () {
                        displayTypingLoader(true);
                        checkRunStatus(runId,data);
                    // }, 500); // Adjust the interval as needed
                }
            },
            error: function (error) {
                console.error("Error checking run status:", error);
            },
        });
    }

    function displayTypingLoader(show) {
        // Show or hide typing loader based on the 'show' parameter
        typingLoaderBlock.style.display = show ? "block" : "none";

        // If showing, append it to the end of the chat
        if (show) {
            chatMessages.appendChild(typingLoaderBlock);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }


    form.addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent the default form submission
        sendMessage();
    });

    sendMessageBtn.addEventListener("click", function () {
        sendMessage();
    });


    function displayMessage(role, content, isAssistant, isLastMessage) {
        const messageContainer = document.createElement("div");
        messageContainer.className = isAssistant ? "direct-chat-msg" : "direct-chat-msg right";
    
        const infoContainer = document.createElement("div");
        infoContainer.className = "direct-chat-info clearfix";
    
        const nameSpan = document.createElement("span");
        nameSpan.className = "direct-chat-name pull-left";
        nameSpan.textContent = role;
    
        infoContainer.appendChild(nameSpan);
    
        const imgElement = document.createElement("img");
        imgElement.className = "direct-chat-img";
        imgElement.src = isAssistant ? "https://img.icons8.com/color/36/000000/administrator-male.png" : "https://img.icons8.com/office/36/000000/person-female.png";
        imgElement.alt = "message user image";
    
        const textContainer = document.createElement("div");
        textContainer.className = "direct-chat-text";
    
        if (isLastMessage) {
            textContainer.innerHTML = `
                <div id="typingLoaderBlock" style="display: none;" class="direct-chat-msg">
                    <div class="direct-chat-info clearfix">
                        <span class="direct-chat-name pull-left">Assistant</span>
                    </div>
                    <img class="direct-chat-img" src="https://img.icons8.com/color/36/000000/administrator-male.png" alt="message user image">
                    <div class="direct-chat-text">
                        <div id="typingLoader">Typing...</div>
                    </div>
                </div>
                <p>${content}</p>
            `;
        } else {
            textContainer.innerHTML = `<p>${content}</p>`;
        }
    
        messageContainer.appendChild(infoContainer);
        messageContainer.appendChild(imgElement);
        messageContainer.appendChild(textContainer);
    
        chatMessages.appendChild(messageContainer);
    
        // Scroll to the bottom of the conversation to show the latest message
        chatMessages.scrollTop = chatMessages.scrollHeight;
    } 
});
