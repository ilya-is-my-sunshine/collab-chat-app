<?php
session_start();
if (isset($_SESSION['Sesh'])){
	header("Location: /collab-chat-app/index.html");
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login_style.css">
    <title>Login</title>
</head>


<body>
      <div class="container">
		<div class="slogan_container">
			<h1>Connectaru</h1>
			<p>Where every conversation sparks a new <br>collaboration and every team thrives.</p>
		</div>
        <div class="box form-box" id='main-form'>
            <form action="" method="post" id='login'>
                <div class="field input">
                    <label for="username">Username</label>
                    <input 
					required
					type="text" 
					name="username" 
					id="username" 
					autocomplete="off"
					/>
					<span style="color: red;" class="error"></span>
					<span class="error-icon"></span>
					<span class="success-icon"></span>
                </div>
                <div class="field input">
                    <label for="password">Password</label>
                    <input 
					required
					minlength="1"
					type="password" 
					name="password"
					id="password" 
					autocomplete="off" 
					/>
					<span style="color: red;" class="error"></span>
					<span class="error-icon"></span>
					<span class="success-icon"></span>
                </div>
                <div class="field">
                    <input type="submit" class="btn" name="submit" value="Login" required>
                </div>  
            </form>
			<a href="forgot_pass_form.php">Forgot Password?</a>
            <br><hr><br>
            
            <?php
                include("database.php");
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    // Close the database connection
                    $username = $_POST["username"];
                    $password = $_POST["password"];
                    
                    if ($username === "" or $password === ""){header("Location: login.html");}
                    // Prepare a parameterized query
                    $stmt = $conn->prepare("SELECT * FROM users_tb WHERE username =?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user_data = $result->fetch_assoc();
                    
                    $hashed_pass = password_hash($password, PASSWORD_DEFAULT); 
                    // Check if there is a matching record
                    if (isset($user_data) &&
                        ($user_data['username'] == $username 
                        && password_verify($user_data['password'], $hashed_pass))) {
                            // Verify the password using a password hashing algorithm
                            $_SESSION['Sesh'] = "IN_SESSION";
                            $_SESSION['username'] = $user_data['username'];
                            
                            header("Location: index.php");
                            
                            session_regenerate_id();
                            exit;		 
                    } else {
                        echo "<div class='message'>
                        <p>WRONG USERNAME OR PASSWORD</p>
                        </div>";
                    }
                    $conn->close();
                }
            ?>
            <form action="sign_up.php" method="post">
                <div class="field">
                    <input type="submit" class="btn" name="submit" value="Create Account" required>
                </div>
            </form>
        </div>
      </div>
</body>
</html>
<script>

</script>