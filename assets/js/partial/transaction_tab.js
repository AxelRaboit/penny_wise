import { attachModalEvents } from '../component/modal.js';

attachModalEvents({
    modalSelector: '#deleteModal',
    triggerButtonSelector: '.deleteButton',
    confirmButtonSelector: '#confirmDelete',
    cancelButtonSelector: '#cancelDelete',
});
