@if(Auth::check() && Auth::user()->perm('chatbot', 'view'))
<div id="chatbot-container">
    <div id="chatbot-button" onclick="toggleChatbot()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="32" height="32">
            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
            <circle cx="12" cy="10" r="1.5"/>
            <circle cx="8" cy="10" r="1.5"/>
            <circle cx="16" cy="10" r="1.5"/>
        </svg>
    </div>
    <div id="chatbot-window" style="display: none;">
        <div class="chatbot-header">
            <div class="header-content">
                <div class="bot-avatar">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="24" height="24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <div class="header-text">
                    <div class="header-title">GPS Assistant</div>
                    <div class="header-status">
                        <span class="status-dot"></span>
                        <span>Online</span>
                    </div>
                </div>
            </div>
            <span class="close-btn" onclick="toggleChatbot()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="24" height="24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </span>
        </div>
        <div class="chatbot-messages" id="chatbot-messages">
            <div class="message bot">
                <div class="message-content">
                    <div class="bot-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </div>
                    <div class="message-text">
                        <div class="message-bubble">👋 Hello! I'm your GPS tracking assistant. Ask me about your devices, locations, fuel data, or history!</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="chatbot-input">
            <input type="text" id="chatbot-input-field" placeholder="Ask about your devices..." onkeypress="handleKeyPress(event)">
            <button onclick="sendMessage()" id="send-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" width="20" height="20">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </div>
        <div class="chatbot-footer">
            <span class="footer-text">Powered by AI • <span id="message-count">0</span> messages</span>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    
    #chatbot-container {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    
    #chatbot-button {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    #chatbot-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    #chatbot-button:hover {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 12px 32px rgba(102, 126, 234, 0.6);
    }
    
    #chatbot-button:hover::before {
        opacity: 1;
    }
    
    #chatbot-button:active {
        transform: scale(0.95);
    }
    
    #chatbot-window {
        width: 380px;
        height: 580px;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15), 0 0 1px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        position: absolute;
        bottom: 80px;
        right: 0;
        overflow: hidden;
        animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .chatbot-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .header-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .bot-avatar {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
    }
    
    .header-text {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .header-title {
        font-weight: 600;
        font-size: 16px;
        letter-spacing: -0.2px;
    }
    
    .header-status {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        opacity: 0.9;
    }
    
    .status-dot {
        width: 8px;
        height: 8px;
        background: #4ade80;
        border-radius: 50%;
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .close-btn {
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .close-btn:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .chatbot-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%);
        scroll-behavior: smooth;
    }
    
    .chatbot-messages::-webkit-scrollbar {
        width: 6px;
    }
    
    .chatbot-messages::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .chatbot-messages::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    
    .chatbot-messages::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    .message {
        margin-bottom: 16px;
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .message-content {
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    
    .bot-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: white;
    }
    
    .message-text {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .message-bubble {
        padding: 12px 16px;
        border-radius: 12px;
        line-height: 1.5;
        font-size: 14px;
        word-wrap: break-word;
        white-space: pre-wrap;
    }
    
    .message.bot .message-bubble {
        background: #f1f5f9;
        color: #1e293b;
        border-bottom-left-radius: 4px;
    }
    
    .message.user {
        display: flex;
        justify-content: flex-end;
    }
    
    .message.user .message-bubble {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom-right-radius: 4px;
        max-width: 75%;
    }
    
    .chatbot-input {
        padding: 16px 20px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 10px;
        background: white;
    }
    
    .chatbot-input input {
        flex: 1;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        outline: none;
        font-size: 14px;
        transition: all 0.2s ease;
        font-family: inherit;
        color: #1e293b;
    }
    
    .chatbot-input input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .chatbot-input button {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    
    .chatbot-input button:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .chatbot-input button:active {
        transform: scale(0.95);
    }
    
    .chatbot-footer {
        padding: 12px 20px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        text-align: center;
    }
    
    .footer-text {
        font-size: 11px;
        color: #64748b;
    }
    
    /* Loading animation */
    #chatbot-loading {
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    
    #chatbot-loading .message-bubble {
        display: flex;
        gap: 4px;
        padding: 16px;
    }
    
    #chatbot-loading .message-bubble::after {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #94a3b8;
        border-radius: 50%;
        animation: typing 1.4s infinite;
    }
    
    #chatbot-loading .message-bubble::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #94a3b8;
        border-radius: 50%;
        animation: typing 1.4s infinite 0.2s;
    }
    
    @keyframes typing {
        0%, 60%, 100% {
            transform: translateY(0);
            opacity: 0.7;
        }
        30% {
            transform: translateY(-10px);
            opacity: 1;
        }
    }
    
    /* Responsive */
    @media (max-width: 480px) {
        #chatbot-window {
            width: calc(100vw - 32px);
            height: calc(100vh - 100px);
            bottom: 80px;
            right: 16px;
        }
    }
</style>

<script>
    // Store conversation history
    var conversationHistory = [];
    var messageCount = 0;
    
    function toggleChatbot() {
        var window = document.getElementById('chatbot-window');
        if (window.style.display === 'none') {
            window.style.display = 'flex';
        } else {
            window.style.display = 'none';
        }
    }

    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }

    function sendMessage() {
        var inputField = document.getElementById('chatbot-input-field');
        var message = inputField.value.trim();
        if (message === '') return;

        addMessage(message, 'user');
        inputField.value = '';
        messageCount++;
        updateMessageCount();
        
        // Add user message to conversation history
        conversationHistory.push({
            role: 'user',
            content: message
        });

        // Show loading
        var loadingDiv = document.createElement('div');
        loadingDiv.id = 'chatbot-loading';
        loadingDiv.classList.add('message', 'bot');
        loadingDiv.innerHTML = `
            <div class="message-content">
                <div class="bot-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <div class="message-text">
                    <div class="message-bubble"></div>
                </div>
            </div>
        `;
        document.getElementById('chatbot-messages').appendChild(loadingDiv);
        scrollToBottom();

        // Send to backend with conversation history
        $.ajax({
            url: '/chatbot/send',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                message: message,
                history: JSON.stringify(conversationHistory)
            },
            success: function(response) {
                // Remove loading
                var loading = document.getElementById('chatbot-loading');
                if (loading) loading.remove();

                console.log("Chatbot response:", response);
                if (response.status === 'success') {
                    addMessage(response.message, 'bot');
                    
                    // Add bot response to conversation history
                    conversationHistory.push({
                        role: 'assistant',
                        content: response.message
                    });
                    messageCount++;
                    updateMessageCount();
                } else {
                    addMessage('Error: ' + response.message, 'bot');
                }
            },
            error: function(xhr, status, error) {
                // Remove loading
                var loading = document.getElementById('chatbot-loading');
                if (loading) loading.remove();

                console.error("Chatbot Error:", status, error, xhr.responseText);
                addMessage('Sorry, something went wrong. Please try again.', 'bot');
            }
        });
    }

    function addMessage(text, sender) {
        var messagesContainer = document.getElementById('chatbot-messages');
        var messageDiv = document.createElement('div');
        messageDiv.classList.add('message', sender);
        
        if (sender === 'bot') {
            messageDiv.innerHTML = `
                <div class="message-content">
                    <div class="bot-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </div>
                    <div class="message-text">
                        <div class="message-bubble">${escapeHtml(text)}</div>
                    </div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="message-bubble">${escapeHtml(text)}</div>
            `;
        }
        
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }
    
    function scrollToBottom() {
        var messagesContainer = document.getElementById('chatbot-messages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    function updateMessageCount() {
        document.getElementById('message-count').textContent = messageCount;
    }
    
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endif
