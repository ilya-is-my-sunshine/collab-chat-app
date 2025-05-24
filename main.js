const APP_ID = "fdf017c1de1f4f708ffbae9b57d79485"
const client = AgoraRTC.createClient({mode:'rtc', codec:'vp8'})

let localTracks = []
let remoteUsers = {}
let currentRoomCode = '';


let joinAndDisplayLocalStream = async () => {

    client.on('user-published', handleUserJoined)
    
    client.on('user-left', handleUserLeft)
    
    let UID = await client.join(APP_ID, currentRoomCode, null, null)

    localTracks = await AgoraRTC.createMicrophoneAndCameraTracks() 

    let player = `<div class="video-container" id="user-container-${UID}">
                        <div class="video-player" id="user-${UID}"></div>
                  </div>`
    document.getElementById('video-streams').insertAdjacentHTML('beforeend', player)

    localTracks[1].play(`user-${UID}`)
    
    await client.publish([localTracks[0], localTracks[1]])
}

let joinStream = async () => {
    // Get room code from input or previously generated one
    const inputCode = document.getElementById("room-input").value.trim();
    currentRoomCode = inputCode || currentRoomCode;
    document.getElementById('chatToggleBtn').style.display = 'block';

    currentRoomCode = inputCode;

    await joinAndDisplayLocalStream();
    document.getElementById('room-tab-container').style.display = 'none';
    document.getElementById('stream-controls').style.display = 'flex';

    await localTracks[0].setMuted(true);
    document.getElementById('mic-btn').innerText = 'Mic off';
    document.getElementById('mic-btn').style.backgroundColor = '#EE4B2B';

};


let handleUserJoined = async (user, mediaType) => {
    remoteUsers[user.uid] = user 
    await client.subscribe(user, mediaType)

    if (mediaType === 'video'){
        let player = document.getElementById(`user-container-${user.uid}`)
        if (player != null){
            player.remove()
        }

        player = `<div class="video-container" id="user-container-${user.uid}">
                        <div class="video-player" id="user-${user.uid}"></div> 
                 </div>`
        document.getElementById('video-streams').insertAdjacentHTML('beforeend', player)

        user.videoTrack.play(`user-${user.uid}`)
    }

    if (mediaType === 'audio'){
        user.audioTrack.play()
    }
}

let handleUserLeft = async (user) => {
    delete remoteUsers[user.uid]
    document.getElementById(`user-container-${user.uid}`).remove()
}

let leaveAndRemoveLocalStream = async () => {
    for(let i = 0; localTracks.length > i; i++){
        localTracks[i].stop()
        localTracks[i].close()
    }

    await client.leave()
    document.getElementById('room-tab-container').style.display = 'block'
    document.getElementById('stream-controls').style.display = 'none'
    document.getElementById('video-streams').innerHTML = ''
    document.getElementById('chatToggleBtn').style.display = 'none';
    document.getElementById('chatSidebar').classList.remove('open');

}

let toggleMic = async (e) => {
    if (localTracks[0].muted){
        await localTracks[0].setMuted(false)
        e.target.innerText = 'Mic on'
        e.target.style.backgroundColor = 'cadetblue'
    }else{
        await localTracks[0].setMuted(true)
        e.target.innerText = 'Mic off'
        e.target.style.backgroundColor = '#EE4B2B'
    }
}

let toggleCamera = async (e) => {
    if(localTracks[1].muted){
        await localTracks[1].setMuted(false)
        e.target.innerText = 'Camera on'
        e.target.style.backgroundColor = 'cadetblue'
    }else{
        await localTracks[1].setMuted(true)
        e.target.innerText = 'Camera off'
        e.target.style.backgroundColor = '#EE4B2B'
    }
}

document.getElementById('join-btn').addEventListener('click', joinStream);
document.getElementById('leave-btn').addEventListener('click', leaveAndRemoveLocalStream);
document.getElementById('mic-btn').addEventListener('click', toggleMic);
document.getElementById('camera-btn').addEventListener('click', toggleCamera);

const chatToggleBtn = document.getElementById('chatToggleBtn');
const chatSidebar = document.getElementById('chatSidebar');
const chatCloseBtn = document.getElementById('chatCloseBtn');
const chatForm = document.getElementById('chatForm');
const chatInput = document.getElementById('chatInput');
const chatMessages = document.getElementById('chatMessages');

function appendMessage(text) {
    const msgDiv = document.createElement('div');
    msgDiv.className = 'chat-message';
    msgDiv.innerText = text;
    chatMessages.appendChild(msgDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Show/hide chat sidebar
if (chatToggleBtn && chatSidebar) {
    chatToggleBtn.addEventListener('click', () => {
        chatSidebar.classList.add('open');
        chatToggleBtn.style.display = 'none';
    });
}

if (chatCloseBtn && chatSidebar && chatToggleBtn) {
    chatCloseBtn.addEventListener('click', () => {
        chatSidebar.classList.remove('open');
        setTimeout(() => {
            chatToggleBtn.style.display = 'block';
        }, 300);
    });
}

// Handle sending messages
chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const msg = chatInput.value.trim();
    if (msg) {
        appendMessage(`You: ${msg}`);
        chatInput.value = '';
    }
});

// Replace with your Agora RTM App ID for chat (different from your RTC APP_ID)
const RTM_APP_ID = "b06aeee237ac4a63861bbe54a01a621a";

let rtmClient;
let rtmChannel;
let rtmUserId;

// Initialize RTM client and setup channel for chat
async function initChat() {
    if (!currentRoomCode) return;  // no room joined yet
    
    // Create RTM client
    rtmClient = AgoraRTM.createInstance(RTM_APP_ID);
    
    // Use random user ID for RTM (or generate from RTC UID if you want)
    rtmUserId = 'user_' + Math.floor(Math.random() * 100000);
    
    try {
        await rtmClient.login({ uid: rtmUserId });
        rtmChannel = await rtmClient.createChannel(currentRoomCode);
        
        // Listen for new messages
        rtmChannel.on('ChannelMessage', ({ text }, senderId) => {
            appendMessage(`User ${senderId}: ${text}`);
        });
        
        await rtmChannel.join();
        appendMessage('You joined chat channel: ' + currentRoomCode);
        
    } catch (err) {
        console.error('RTM login/join error:', err);
        appendMessage('Chat connection failed.');
    }
}

// Leave chat when leaving the video room
async function leaveChat() {
    console.log("Leaving chat...");
    if (rtmChannel) {
        await rtmChannel.leave();
        rtmChannel = null;
    }
    if (rtmClient) {
        await rtmClient.logout();
        rtmClient = null;
    }
    while (chatMessages.firstChild) {
    chatMessages.removeChild(chatMessages.firstChild);
}
    chatMessages.innerHTML = ''; // clear chat on leave
    chatInput.value = '';     
}

// Override your chat send message to use RTM
chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = chatInput.value.trim();
    if (msg && rtmChannel) {
        try {
            await rtmChannel.sendMessage({ text: msg });
            appendMessage(`You: ${msg}`);
            chatInput.value = '';
        } catch (err) {
            console.error('Send message error:', err);
        }
    }
});

// When user joins room in your existing joinStream function,
// call initChat();
const originalJoinStream = joinStream;
joinStream = async () => {
    await originalJoinStream();
    await initChat();
};

// When user leaves room in your existing leaveAndRemoveLocalStream function,
// call leaveChat();
const originalLeaveStream = leaveAndRemoveLocalStream;
leaveAndRemoveLocalStream = async () => {
    await originalLeaveStream();
    await leaveChat();
};

