<div id="chatbot-container">
    <div id="chat-icon" onclick="toggleChat()">
        <ion-icon name="car-sport-outline" style="font-size: 24px;"></ion-icon>
    </div>

    <div id="chat-window">
        <div id="chat-header">
            <span>Car Rental Assistant</span>
            <button onclick="toggleChat()">✖</button>
        </div>
        <div id="chat-messages">
            <p class="bot-msg">Hello! Need help renting a car?</p>
        </div>
        <div id="chat-options"></div>
        <div id="chat-input-area">
            <input type="text" id="chat-input" placeholder="Type your message..." onkeydown="if(event.key==='Enter') sendMessage()">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="css/chatbot.css">
<script src="javascript/chatbot.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>