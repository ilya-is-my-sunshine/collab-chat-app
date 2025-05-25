<html lang="en">
	<?php
		session_start();
		if (isset($_SESSION['Sesh'])){
			header("Location: index.php");
		}
	?>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="sign_up_style.css">
    <title>Sign Up for Connectaru</title>
</head>
<body>
      <div class="container">
        <div class="box form-box" id='main-form'>
            <h1>Create a new account</h1>
			<hr><br>
            <form action="" method="post" id='sign_up'>
                <div class="field input">
                    <label for="username">Username</label>
                    <input 
					required
					minlength="3"
					custommaxlength="20"
					type="text" 
					name="username" 
					id="username" 
					autocomplete="off"
					/>
					<span class="error"></span>
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
					<span class="error"></span>
					<span class="error-icon"></span>
					<span class="success-icon"></span>
                </div>
				<div class="field input">
                    <label for="confirm_Password">Confirm Password</label>
                    <input 
					required
					minlength="1"
					type="password" 
					name="confirm_password"
					id="confirm_password" 
					match="password"
					autocomplete="off" 
					/>
					<span class="error"></span>
					<span class="error-icon"></span>
					<span class="success-icon"></span>
                </div>
				<div class="field input">
                    <label for="security_question">Security Question</label>
					<select name="security_question" id="security_question" required>
						<option value="" disabled >Select Security Question</option>
						<option value="1">Where is your hometown?</option>
						<option value="2">What is your pet's name?</option>
						<option value="3">Who is your favorite anime character?</option>
					</select>
					<span class="error"></span>
					<span class="error-icon"></span>
					<span class="success-icon"></span>
                </div>
                <div class="field input">
                    <label for="security_answer">Security Answer</label>
                    <input 
					required
					type="text" 
					name="security_answer" 
					id="security_answer" 
					autocomplete="off"
					/>
					<span class="error"></span>
					<span class="error-icon"></span>
					<span class="success-icon"></span>
                </div>
				<?php
					include("database.php");
					if ($_SERVER["REQUEST_METHOD"] == "POST") {
						
						if (isset($_POST["username"]) && !empty($_POST["username"]) &&
							isset($_POST["password"]) && !empty($_POST["password"]) &&
							isset($_POST["security_question"]) && !empty($_POST["security_question"]) &&
							isset($_POST["security_answer"]) && !empty($_POST["security_answer"])
						){
							$username = $_POST["username"];
							$password = $_POST["password"];
							$security_question = $_POST["security_question"];
							$security_answer = $_POST["security_answer"];
							
							$verify_query = mysqli_query($conn,"SELECT username FROM users_tb WHERE username='$username'");

							if(mysqli_num_rows($verify_query)!= 0){
								echo "<div class='message'>
										<p>This username is taken <br> Try another One Please!</p>
									</div>";
							} else {
								// Prepare the SQL statement
								$stmt = $conn->prepare("INSERT INTO users_tb(username, password, security_question, security_answer) VALUES (?, ?, ?, ?)");
								$stmt->bind_param("ssss", $username, $password, $security_question, $security_answer);
								// Execute the statement
								if ($stmt->execute()) {
									echo "<div class='message'>
											<p>Account Created Successfully</p>
											</div>";
									// Query executed successfully
								} else {
									echo "<div class='message'>
											<p>Failed to create account</p>
											</div>";// Query failed
								}
								// Close the statement
								$stmt->close();
							}

						}
						
						/*
						if ($username === "" or $password === ""){header("Location: login.php");}
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
						}*/

						$conn->close();
					}
				?>
                <div class="field">
                    <input type="submit" class="btn" name="submit" value="Sign Up" required>
                </div>  
				<a href="login.php">Already have an account?</a>
            </form>
        </div>
      </div>
</body>
</html>
<script>
    const validateForm = (formSelector, callback) => {
	
	const formElement = document.querySelector(formSelector);
	
	const validationOptions = [
		{
			attribute: 'minlength',
			isValid: input => input.value && input.value.length >= parseInt(input.minLength, 10),
			errorMessage: (input, label) => `${label.textContent} needs to be at least ${input.minLength} characters`
		},
		{
			attribute: 'custommaxlength',
			isValid: input => input.value && input.value.length <= parseInt(input.getAttribute('custommaxlength'), 10),
			errorMessage: (input, label) => `${label.textContent} needs to be less than ${input.getAttribute('custommaxlength')} characters`
		},
		{
			attribute: 'pattern',
			isValid: input => {
				const patternRegex = new RegExp(input.pattern);
				return patternRegex.test(input.value);
			},
			errorMessage: (input, label) => `Not a valid ${label.textContent}`,
		},
		{
			attribute: 'match',
			isValid: input => {
				const matchSelector = input.getAttribute('match');
				const matchedElement = formElement.querySelector(`#${matchSelector}`);
				return matchedElement && matchedElement.value.trim() === input.value.trim();
			},
			errorMessage: (input, label) => {
				const matchSelector = input.getAttribute('match');
				const matchedElement = formElement.querySelector(`#${matchSelector}`);
				//return `${label.textContent} should match password`;
				return `passwords should match`;
			},
		},
		{
			attribute: 'required',
			isValid: input => input.value.trim() != '',
			errorMessage: (input, label) => `${label.textContent} is required`
		}
		
	];

	const validateSingleFormGroup = formGroup => {
		const label = formGroup.querySelector('label');
		const input = formGroup.querySelector('input, textarea, select');
		const errorContainer = formGroup.querySelector('.error');
		const errorIcon = formGroup.querySelector('.error-icon');
		const successIcon = formGroup.querySelector('.success-icon');
		let formGroupError = false;
		for(const option of validationOptions){
			if(input.hasAttribute(option.attribute) && !option.isValid(input)){
				errorContainer.textContent = option.errorMessage(input, label);
				input.classList.add('border-red-700');
				input.classList.remove('border-green-700');
				successIcon.classList.add('hidden');
				errorIcon.classList.remove('hidden');
				formGroupError = true;
			}
		}
		
		if (!formGroupError){
			errorContainer.textContent = "";
			successIcon.classList.remove('hidden');
			errorIcon.classList.add('hidden');
			input.classList.add('border-green-700');
			input.classList.remove('border-red-700');
		}
		
		return !formGroupError;
	};
	
	formElement.setAttribute('novalidate', '');
	
	Array.from(formElement.elements).forEach(element => {
		element.addEventListener('blur', event => {
			const field_input = event.srcElement.parentElement;
			if (field_input.classList.contains('field') && field_input.classList.contains('input')) {
				validateSingleFormGroup(field_input);
			}
		});
	});
	
	const validateAllFormGroups = formToValidate => {
		const formGroups = Array.from(
			formToValidate.querySelectorAll('.field.input')
		);
		return formGroups.every(formGroup => validateSingleFormGroup(formGroup));	
	};
	
	formElement.addEventListener('submit', (event) => {
		const formValid = validateAllFormGroups(formElement);
		if(!formValid){
			event.preventDefault();
		} else {
			console.log('Form is valid');
			//event.preventDefault();
		}
	});
};
const sendToServer ="";
validateForm('#sign_up', sendToServer);
</script>