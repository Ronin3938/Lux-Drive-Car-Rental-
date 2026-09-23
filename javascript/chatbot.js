// Toggle chat window
function toggleChat() {
    const chatWindow = document.getElementById('chat-window');
    chatWindow.style.display = chatWindow.style.display === 'flex' ? 'none' : 'flex';
}

// Show clickable option buttons
function showOptions(options) {
    const optionsContainer = document.getElementById('chat-options');
    optionsContainer.innerHTML = '';

    options.forEach(option => {
        const btn = document.createElement('button');
        btn.className = 'chat-option-btn';
        btn.textContent = option;

        btn.onclick = () => {
            sendMessage(option);
            optionsContainer.innerHTML = '';
        };

        optionsContainer.appendChild(btn);
    });
}

// Add user message
function addUserMessage(text) {
    const messages = document.getElementById('chat-messages');
    const userMsg = document.createElement('p');
    userMsg.className = 'user-msg';
    userMsg.textContent = text;
    messages.appendChild(userMsg);
    messages.scrollTop = messages.scrollHeight;
}

// Add bot message
function addBotMessage(text) {
    const messages = document.getElementById('chat-messages');
    const lines = text.split("\n");
    lines.forEach(line => {
        const botMsg = document.createElement('p');
        botMsg.className = 'bot-msg';
        botMsg.textContent = line;
        messages.appendChild(botMsg);
    });
    messages.scrollTop = messages.scrollHeight;
}

// Send message to PHP
function sendMessage(message) {
    const input = document.getElementById('chat-input');
    const msg = message || input.value.trim();
    if (!msg) return;

    addUserMessage(msg);
    if (!message) input.value = '';

    fetch('chatbot_process.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(msg)
    })
    .then(res => res.json())
    .then(data => {
        addBotMessage(data.message);

        if (data.options && data.options.length > 0) {
            showOptions(data.options);
        }
    })
    .catch(() => {
        addBotMessage("Oops! Something went wrong.");
    });
}

// Enter key sends message
document.getElementById('chat-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') sendMessage();
});
