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
        <!-- Add this inside your body tag -->


    <!-- Add username input near your room join form -->


    <div id="room-tab-container">
        <header>
        <div class="logo">
            <img src="logo.png">
            <span>Connectaru</span>
        </div>
        <div class="username" id="username_placeholder">Username</div>
        </header>
        <main>
        <div class="join-box">
            <h2>Room</h2>
            <input type="text" id = "room-input" placeholder="" />
            <button id = "join-btn">Join Room</button>
            <form action="logout.php" method="post">
                <input id="logout-btn" type="submit" value="Logout">
            </form>
        </div>
        </main>
    </div>
    

    <div id="stream-wrapper">
        <div id="text-chat-handler">
            <div id="video-streams"></div>
            <div id="textChatContainer"></div>
            <div id="textChat">
                <div id="chatHeader">Chat Room</div>
                <div id="allChatContainer"></div>
                <div id="userChatContainer">
                    <input id = "chatInput" type="text"><button id="sendBtn"></button>   
                </div>
            </div>
        </div>
        <div id="stream-controls">
            <button id="leave-btn">Leave Stream</button>
            <button id="mic-btn">Mic On</button>
            <button id="camera-btn">Camera on</button>
        </div>
    </div>
    
</body>
<!-- <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script> -->
<script src="AgoraRTC_N-4.23.3.js"></script>
<script>
    const SessionData = <?php echo json_encode($SessionData); ?>;
    console.log(SessionData.username);
    document.getElementById('username_placeholder').innerHTML=SessionData.username;        
</script>
<script src='main.js'></script>
</html>