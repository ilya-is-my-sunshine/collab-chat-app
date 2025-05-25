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

document.getElementById('chatInput').addEventListener('keydown', function(event) {
    if (event.key === "Enter") {
        event.preventDefault(); 
        document.getElementById('sendBtn').click();
    }
});


socket.on('receive-message', ({ message, username: sender }) => {
    const isOwn = sender === username;
    appendMessage(message, sender, isOwn);
});


function appendMessage(msg, sender, isOwnMessage = false) {
    const chatBox = document.getElementById('allChatContainer');
    
    const wrapper = document.createElement('div');
    wrapper.style.display = 'flex';
    wrapper.style.justifyContent = isOwnMessage ? 'flex-end' : 'flex-start';

    const messageWrapper = document.createElement('div');
    messageWrapper.className = 'messageWrapper';

    const senderElem = document.createElement('div');
    senderElem.className = 'senderName';

    senderElem.innerText = isOwnMessage ? 'You' : sender;

    senderElem.style.textAlign = isOwnMessage ? 'right' : 'left';
    senderElem.style.fontWeight = 'bold';
    senderElem.style.fontSize = '0.8em';
    senderElem.style.marginBottom = '2px';

    const messageElem = document.createElement('div');
    messageElem.className = 'messageElem';
    messageElem.innerText = msg;

    const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
    const timeElem = document.createElement('div');
    timeElem.className = 'timestamp';
    timeElem.innerText = time;
    

    // Assemble message parts
    messageWrapper.appendChild(senderElem);
    messageWrapper.appendChild(messageElem);
    wrapper.appendChild(messageWrapper);
    chatBox.appendChild(wrapper);
    messageWrapper.appendChild(timeElem);
    chatBox.scrollTop = chatBox.scrollHeight;
}

document.getElementById('messageIconBtn').addEventListener('click', () => {
    const chatTab = document.getElementById('textChat');
    chatTab.style.display = chatTab.style.display === 'none' ? 'flex' : 'none';
    document.getElementById('textChat').style.display = 'flex';
    document.getElementById('messageIconBtn').style.display = 'none';
});

document.getElementById('closeChatBtn').addEventListener('click', () => {
    document.getElementById('textChat').style.display = 'none';
    document.getElementById('messageIconBtn').style.display = 'flex';
});



let joinAndDisplayLocalStream = async () => {

    client.on('user-published', handleUserJoined)
    
    client.on('user-left', handleUserLeft)
    
    let UID = await client.join(APP_ID, currentRoomCode, null, null)

    localTracks = await AgoraRTC.createMicrophoneAndCameraTracks() 

    let player = `
    <div class="video-container" id="user-container-${UID}">
        <div class="video-player" id="user-${UID}"></div>
    </div>
`;


    document.getElementById('video-streams').insertAdjacentHTML('beforeend', player)

localTracks[1].play(`user-${UID}`);
    
    await client.publish([localTracks[0], localTracks[1]])
}

let joinStream = async () => {
    var inputCode = document.getElementById("room-input").value.trim();
    var inputName = SessionData.username;


    if (!inputName) {
        alert("Please enter your name.");
        return;
    }
    document.getElementById('messageIconBtn').style.display = 'flex'; 

    currentRoomCode = inputCode || currentRoomCode;
    username = inputName;

    await joinAndDisplayLocalStream();

    document.getElementById('room-tab-container').style.display = 'none';
    document.getElementById('stream-controls').style.display = 'flex';
    document.getElementById('stream-wrapper').style.height = "100%";
    document.getElementById('stream-wrapper').style.width = "100%";
    document.getElementById('stream-wrapper').style.display = "grid";
    document.getElementById('messageIconBtn').style.display = 'flex';
    document.getElementById('textChat').style.display = 'flex';
    document.getElementById('messageIconBtn').style.display = 'none';
    socket.emit('join-room', { roomCode: currentRoomCode, username });

    await localTracks[0].setMuted(true);
    document.getElementById('micbtn').src= "micoff.png";
};



let handleUserJoined = async (user, mediaType) => {
    remoteUsers[user.uid] = user 
    await client.subscribe(user, mediaType)

    if (mediaType === 'video'){
        let player = document.getElementById(`user-container-${user.uid}`);
        if (player != null){
            player.remove()
        }

        player = `
  <div class="video-container" id="user-container-${user.uid}">
    <div class="video-player" id="user-${user.uid}"></div> 
  </div>
`;

        document.getElementById('video-streams').insertAdjacentHTML('beforeend', player)

        user.videoTrack.play(`user-${user.uid}`);
    }

    if (mediaType === 'audio'){
        user.audioTrack.play()
    }
}

let handleUserLeft = async (user) => {
    delete remoteUsers[user.uid]
    document.getElementById(`user-container-${user.uid}`).remove();
}

let leaveAndRemoveLocalStream = async () => {
    for(let i = 0; localTracks.length > i; i++){
        localTracks[i].stop()
        localTracks[i].close()
    }

    await client.leave()
    document.getElementById('camerabtn').src= "camera.png";
    document.getElementById('room-tab-container').style.display = 'flex'
    document.getElementById('stream-controls').style.display = 'none'
    document.getElementById('stream-wrapper').style.height = "0";
    document.getElementById('stream-wrapper').style.width = "0";
    document.getElementById('stream-wrapper').style.display = "none";
    document.getElementById('video-streams').innerHTML = ''
    document.getElementById('allChatContainer').innerHTML = '';
    document.getElementById('messageIconBtn').style.display = 'none'; 
    document.getElementById('textChat').style.display = 'none'; 
}

let toggleMic = async (e) => {
    if (localTracks[0].muted){
        await localTracks[0].setMuted(false)
        document.getElementById('micbtn').src= "mic.png";
    }else{
        await localTracks[0].setMuted(true)
        document.getElementById('micbtn').src= "micoff.png";
    }
}

let toggleCamera = async (e) => {
    if(localTracks[1].muted){
        await localTracks[1].setMuted(false)
        document.getElementById('camerabtn').src= "camera.png";
    }else{
        await localTracks[1].setMuted(true)
        document.getElementById('camerabtn').src= "cameraoff.png";
    }
}





document.getElementById('join-btn').addEventListener('click', joinStream);
document.getElementById('leave-btn').addEventListener('click', leaveAndRemoveLocalStream);
document.getElementById('mic-btn').addEventListener('click', toggleMic);
document.getElementById('camera-btn').addEventListener('click', toggleCamera);
