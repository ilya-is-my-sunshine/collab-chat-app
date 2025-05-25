<html lang="en">
    <?php
        session_start();
        if (isset($_SESSION['Sesh'])){
            header("Location: index.php");
        }

        // Initialize variables for PHP logic below the HTML structure
        $registration_success = false;
        $username_taken_error = false;
        $general_registration_error = false;
        $registered_username = ''; // To display the username if successful
    ?>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="sign_up_style.css">
    <title>Sign Up for Connectaru</title>
    <style>
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .message p {
            margin: 0;
            font-weight: bold;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .redirect-info {
            margin-top: 15px;
            font-size: 0.9em;
            color: #555;
        }

		.redirect-link {
            color: #171717;
        }

		.redirect-link:hover {
            color:rgb(73, 79, 131);
        }
    </style>
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
                        <option value="" disabled selected>Select Security Question</option>
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
                    // PHP code for processing form submission
                    include("database.php"); // Ensure database.php is correctly linked

                    if ($_SERVER["REQUEST_METHOD"] == "POST") {

                        // Basic validation that all fields are set and not empty
                        if (isset($_POST["username"]) && !empty($_POST["username"]) &&
                            isset($_POST["password"]) && !empty($_POST["password"]) &&
                            isset($_POST["security_question"]) && !empty($_POST["security_question"]) &&
                            isset($_POST["security_answer"]) && !empty($_POST["security_answer"])
                        ){
                            $username = $_POST["username"];
                            // IMPORTANT: You should hash the password here before storing!
                            // For example: $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
                            $password = $_POST["password"]; // Currently storing plain, please hash!
                            $security_question = $_POST["security_question"];
                            $security_answer = $_POST["security_answer"];

                            // Check if username already exists
                            // Using prepared statement for SELECT to prevent SQL injection
                            $verify_stmt = $conn->prepare("SELECT username FROM users_tb WHERE username=?");
                            if ($verify_stmt) {
                                $verify_stmt->bind_param("s", $username);
                                $verify_stmt->execute();
                                $verify_result = $verify_stmt->get_result();

                                if($verify_result->num_rows != 0){
                                    $username_taken_error = true;
                                } else {
                                    // Prepare the SQL statement for insertion
                                    $insert_stmt = $conn->prepare("INSERT INTO users_tb(username, password, security_question, security_answer) VALUES (?, ?, ?, ?)");
                                    if ($insert_stmt) {
                                        $insert_stmt->bind_param("ssss", $username, $password, $security_question, $security_answer);
                                        // Execute the statement
                                        if ($insert_stmt->execute()) {
                                            $registration_success = true; // Set flag for successful registration
                                            $registered_username = $username; // Store username for display
                                        } else {
                                            $general_registration_error = true;
                                        }
                                        $insert_stmt->close();
                                    } else {
                                        $general_registration_error = true; // Error preparing insert statement
                                    }
                                }
                                $verify_stmt->close();
                            } else {
                                $general_registration_error = true; // Error preparing verify statement
                            }

                        } else {
                            // This part handles cases where not all fields are set (e.g., due to client-side bypass)
                            // In a real app, you'd add more specific error messages here.
                            $general_registration_error = false;
                        }
                    }
                    $conn->close();
                ?>
                <div class="field">
                    <input type="submit" class="btn" name="submit" value="Sign Up" required>
                </div>
                <a href="login.php">Already have an account?</a>
            </form>

            <?php if ($registration_success): ?>
                <div class='message success-message'>
                    <p>Account Created Successfully!</p>
                    <p class="redirect-info">Welcome, <?php echo htmlspecialchars($registered_username); ?>! You'll be redirected in <span id="countdown">5</span> seconds.</p>
                    <p class="redirect-info">If you are not redirected automatically, <a href="login.php" class="redirect-link">click here</a>.</p>
                </div>
                <script>
                    const redirectURL = "login.php";
                    let countdownElement = document.getElementById("countdown");
                    let countdown = 5;

                    function updateCountdown() {
                        countdownElement.textContent = countdown;
                        if (countdown <= 0) { // Use <=0 to ensure it shows 0 then redirects
                            window.location.href = redirectURL;
                        } else {
                            countdown--;
                            setTimeout(updateCountdown, 1000);
                        }
                    }

                    document.addEventListener('DOMContentLoaded', updateCountdown);
                </script>
            <?php elseif ($username_taken_error): ?>
                <div class='message error-message'>
                    <p>This username is taken. Try another one please!</p>
                </div>
            <?php elseif ($general_registration_error && $_SERVER["REQUEST_METHOD"] == "POST"): ?>
                <div class='message error-message'>
                    <p>Failed to create account. Please ensure all fields are filled correctly.</p>
                </div>
            <?php endif; ?>
        </div>
      </div>
</body>
</html>
<script>
    const validateForm = (formSelector) => { // Removed callback parameter as it's not used
    
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
            errorMessage: (input, label) => `passwords should match`,
        },
        {
            attribute: 'required',
            isValid: input => {
                if (input.tagName === 'SELECT') {
                    // For select: value should not be empty or the disabled default option's value
                    return input.value.trim() !== '';
                }
                return input.value.trim() !== '';
            },
            errorMessage: (input, label) => `${label.textContent} is required`
        }
        
    ];

    const validateSingleFormGroup = formGroup => {
        const label = formGroup.querySelector('label');
        const input = formGroup.querySelector('input, textarea, select'); // Include select
        const errorContainer = formGroup.querySelector('.error');
        const errorIcon = formGroup.querySelector('.error-icon');
        const successIcon = formGroup.querySelector('.success-icon');
        let formGroupError = false;

        // Reset previous states
        errorContainer.textContent = "";
        if (input) { // Check if input exists before manipulating classes
            input.classList.remove('border-red-700', 'border-green-700');
        }
        successIcon.classList.add('hidden');
        errorIcon.classList.add('hidden');

        if (!input) {
            console.warn('Validation skipped: No input, textarea, or select found in formGroup:', formGroup);
            return true; // Treat as valid if no target element
        }

        for(const option of validationOptions){
            if(input.hasAttribute(option.attribute) && !option.isValid(input)){
                errorContainer.textContent = option.errorMessage(input, label);
                input.classList.add('border-red-700');
                input.classList.remove('border-green-700'); // Ensure green is removed
                successIcon.classList.add('hidden');
                errorIcon.classList.remove('hidden');
                formGroupError = true;
                break; // Stop checking further options for this input
            }
        }
        
        if (!formGroupError){
            errorContainer.textContent = "";
            successIcon.classList.remove('hidden');
            errorIcon.classList.add('hidden');
            input.classList.add('border-green-700');
            input.classList.remove('border-red-700'); // Ensure red is removed
        }
        
        return !formGroupError;
    };
    
    formElement.setAttribute('novalidate', '');
    
    Array.from(formElement.elements).forEach(element => {
        // Find the closest parent div with both 'field' and 'input' classes
        const field_input = element.closest('.field.input'); // Use closest for robustness
        if (field_input) {
            element.addEventListener('blur', event => {
                validateSingleFormGroup(field_input);
            });
            // Add 'change' listener for select elements
            if (element.tagName === 'SELECT') {
                element.addEventListener('change', event => {
                    validateSingleFormGroup(field_input);
                });
            }
        }
    });
    
    const validateAllFormGroups = formToValidate => {
        const formGroups = Array.from(
            formToValidate.querySelectorAll('.field.input')
        );
        let allValid = true; // Use a flag to track overall validity
        formGroups.forEach(formGroup => {
            if (!validateSingleFormGroup(formGroup)) {
                allValid = false; // If any group is invalid, set flag to false
            }
        });
        return allValid;
    };
    
    formElement.addEventListener('submit', (event) => {
        const formValid = validateAllFormGroups(formElement);
        if(!formValid){
            event.preventDefault();
            console.log('Client-side validation failed. Form not submitted.');
        } else {
            console.log('Client-side validation passed. Form will submit.');
            // No event.preventDefault() here, so the form will submit normally.
        }
    });
};
// Removed the 'sendToServer' parameter as it's not being used as a callback
validateForm('#sign_up');
</script>