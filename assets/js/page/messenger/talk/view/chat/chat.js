const scrollToBottom = () => {
    const messagesContainer = document.getElementById('js-messenger-messages-container');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
};

export const initializeChat = () => {
    scrollToBottom();
};