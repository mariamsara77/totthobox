import './quil-editor';   // শুধু এখানে
// দরকার হলে echoও এখানে নিতে পারো
// import './echo';

import { syncDataWithServer } from './offline-handler';

document.addEventListener('livewire:navigated', () => {
    syncDataWithServer();
});

syncDataWithServer();