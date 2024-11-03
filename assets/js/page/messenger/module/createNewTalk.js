import {attachModalEvents} from "@component/modal/modal";

const createNewTalk = () => {
    const modalFriendListContent = document.getElementById('js-modal-friend-list-content').innerHTML;

    attachModalEvents({
        modalId: 'addConversationModal',
        modalTitle: 'Start a New Conversation',
        modalMessageHeader: 'Choose a friend to start a conversation.',
        modalContent: modalFriendListContent,
        triggerButtonSelector: '#js-add-talk-button',
        isDeleteAction: false,
    });
};

export const initializeCreateNewTalk = () => {
    createNewTalk();
};