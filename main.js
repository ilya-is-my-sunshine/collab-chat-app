const APP_ID = "fdf017c1de1f4f708ffbae9b57d79485"
const client = AgoraRTC.createClient({mode:'rtc', codec:'vp8'})

let localTracks = []
let remoteUsers = {}
let currentRoomCode = '';
let username = '';
const socket = io('https://video-chat-server-8i0f.onrender.com');


document.getElementById('sendBtn').addEventListener('click', () => {
    const message = document.getElementById('chatInput').value.trim();
    if (message) {
        socket.emit('send-message', {
            roomCode: currentRoomCode,
            message,
            username
        });
        document.getElementById('chatInput').value = '';
    }
});


socket.on('receive-message', ({ message, username: sender }) => {
    const isOwn = sender === username;
    appendMessage(message, sender, isOwn);
});


function appendMessage(msg, sender, isOwnMessage = false) {
    const chatBox = document.getElementById('allChatContainer');
    
    // Wrapper aligns message left or right
    const wrapper = document.createElement('div');
    wrapper.style.display = 'flex';
    wrapper.style.justifyContent = isOwnMessage ? 'flex-end' : 'flex-start';

    // Inner wrapper stacks name and bubble vertically
    const messageWrapper = document.createElement('div');
    messageWrapper.className = 'messageWrapper';

    // Sender name
    const senderElem = document.createElement('div');
    senderElem.className = 'senderName';
    senderElem.innerText = sender;

    // Message bubble
    const messageElem = document.createElement('div');
    messageElem.className = 'messageElem';
    messageElem.innerText = msg;

    // Assemble message parts
    messageWrapper.appendChild(senderElem);
    messageWrapper.appendChild(messageElem);
    wrapper.appendChild(messageWrapper);
    chatBox.appendChild(wrapper);

    chatBox.scrollTop = chatBox.scrollHeight;
}




let joinAndDisplayLocalStream = async () => {

    client.on('user-published', handleUserJoined)
    
    client.on('user-left', handleUserLeft)
    
    let UID = await client.join(APP_ID, currentRoomCode, null, null)

    localTracks = await AgoraRTC.createMicrophoneAndCameraTracks() 

    let player = `<div class="video-container" id="user-container-${UID}">
                    <div class="video-player" id="user-${UID}"></div>
                </div>`;

    document.getElementById('video-streams').insertAdjacentHTML('beforeend', player)

    localTracks[1].play(`user-${UID}`)
    
    await client.publish([localTracks[0], localTracks[1]])
}

let joinStream = async () => {
    var inputCode = document.getElementById("room-input").value.trim();
    var inputName = document.getElementById("username-input").value.trim();

    if (!inputName) {
        alert("Please enter your name.");
        return;
    }

    currentRoomCode = inputCode || currentRoomCode;
    username = inputName;

    await joinAndDisplayLocalStream();

    document.getElementById('room-tab-container').style.display = 'none';
    document.getElementById('stream-controls').style.display = 'flex';
    document.getElementById('stream-wrapper').style.height = "100%";
    document.getElementById('stream-wrapper').style.width = "100%";
    document.getElementById('stream-wrapper').style.display = "grid";

    socket.emit('join-room', { roomCode: currentRoomCode, username });

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
                 </div>`;
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
    document.getElementById('room-tab-container').style.display = 'flex'
    document.getElementById('stream-controls').style.display = 'none'
    document.getElementById('stream-wrapper').style.height = "0";
    document.getElementById('stream-wrapper').style.width = "0";
    document.getElementById('stream-wrapper').style.display = "none";
    document.getElementById('video-streams').innerHTML = ''
    document.getElementById('allChatContainer').innerHTML = '';

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