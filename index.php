<!DOCTYPE html>
<?php
session_start();
if (!isset($_SESSION['Sesh'])){
	header("Location: login.php");
}else{
    $SessionData = $_SESSION;
}

?>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Connectaru</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' media='screen' href='main.css'>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
</head>
<body>
    <div id="room-tab-container">
        <header>
        <div class="logo">
            <img src="logo.png">
            <span>Connectaru</span>
        </div>
        <div class="username" id="username_placeholder">Username</div>
        </header>
        <main id = "main">
        <div class="join-box">
            <h2>Room</h2>
            <input type="text" id = "room-input" placeholder="insert unique room code here..."/>
            <button id = "join-btn">Join Room</button>
            <form action="logout.php" method="post">
                <button id="logout-btn" type="submit">Logout</button>
            </form>
        </div>
        </main>
    </div>
    

    <div id="stream-wrapper">
        <header>
            <div class="logo">
                <img src="logo.png">
                <span>Connectaru</span>
            </div>
            <div class="username" id="username_placehold">Username</div>
        </header>
        <main>
        <div id="text-chat-handler">
            <div id="video-streams"></div>
            <div id="textChatContainer"></div>
        </div>
        <div id="stream-controls">
            <button id="leave-btn"><image image id = "leavebtn" src = "endcall.png" height = "70vw"></button>    
            <button id="mic-btn"><image id = "micbtn" src = "micoff.png" height = "70vw"></button>
            <button id="camera-btn"><image image id = "camerabtn" src = "camera.png" height = "70vw"></button>
        </div>
    </main>
    </div>


    <div id="chatIconContainer">
        <button id="messageIconBtn">💬</button>
    </div>
    <div id="textChat">
        <div id = "chatHeader">
        Chat Room
        <button id="closeChatBtn" title="Close Chat">×</button>
        </div>
        <div id="allChatContainer"></div>
        <div id="userChatContainer">
            <input id="chatInput" type="text" placeholder="Type a message...">
            <button id="sendBtn">Send</button> 
        </div>
    </div>


</body>
<!-- <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script> -->
<script src="AgoraRTC_N-4.23.3.js"></script>
<script>
    const SessionData = <?php echo json_encode($SessionData); ?>;
    console.log(SessionData.username);
    document.getElementById('username_placeholder').innerHTML=SessionData.username;
    document.getElementById('username_placehold').innerHTML=SessionData.username;      
</script>
<script src='main.js'></script>
</html>
