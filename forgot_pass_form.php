<?php
session_start();
include("database.php");
if (isset($_SESSION['Sesh'])){
	header("Location: index.php");
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
        <div class="box form-box" id='main-form'>
            <header>
                <div class="logo">
                    <img src="logo.png">
                    <span>Connectaru</span>
                </div>
            </header>
            <h1>Account Recovery</h1>
            <form action="" method="post" id='forgot_pass'>
            <?php

            $endingPart = <<<'EOD'
            <div class="field">
            <input type="submit" class="btn" name="submit" value="Next" required>
            </div>  
            </form>
            <a href="login.php" id="backtologin">Back to login</a>
            </div>

            </div>
            </body>
            </html>
            EOD;

            $questions = array (
                "1" => "Where is your hometown?",
                "2" => "What is your pet's name?",
                "3" => "Who is your favorite anime character?"
            );
            
            if (isset($_POST['username']) && $_SESSION['recovery_step']==0){
                $user = $_POST['username'];
                $user_exist = mysqli_query($conn, "SELECT * FROM users_tb WHERE username ='$user'");

                if(mysqli_num_rows($user_exist) != 0){ //USER EXISTS
                    $_SESSION['recovery_step'] = 1;
                    $_SESSION['recovery_user_details'] = mysqli_fetch_assoc($user_exist);
                    $row = $_SESSION['recovery_user_details'] ;
                    
                    $myQuestion = $questions[$row['security_question']];

                    echo <<<HTML
                    <div class="field input">
                        <label for="security_answer">$myQuestion</label>
                        <input 
                            required
                            type="text" 
                            name="security_answer" 
                            id="security_answer" 
                            autocomplete="off"
                        />
                    </div>
                    HTML;
                    echo $endingPart;
                } else { //USER NOT EXISTS
                    echo <<<HTML
                    <div class="field input">
                        <label for="username">Username</label>
                        <input 
                            required
                            type="text" 
                            name="username" 
                            id="username" 
                            autocomplete="off"
                        />
                    </div>
                    <div class='message'>
                        <p>NON EXISTENT USER</p>
                        </div>
                    HTML;
                    echo $endingPart;

                }
            } else if (isset($_POST['security_answer']) && $_SESSION['recovery_step']==1){
                $row = $_SESSION['recovery_user_details'] ;

                $myQuestion = $questions[$row['security_question']];
                
                if( $row['security_answer'] == $_POST['security_answer']){//changing of password
                    $_SESSION['recovery_step']=2;
                    echo <<<HTML
                    <div class="field input">
                        <label for="new_password">New Password</label>
                        <input 
                            required
                            minlength="1"
                            type="pass" 
                            name="new_password" 
                            id="new_password" 
                            autocomplete="off"
                        />
                        <span class="error"></span>
                        <span class="error-icon"></span>
                        <span class="success-icon"></span>
                    </div>
                    <div class="field input">
                        <label for="confirm_new_password">Confirm New Password</label>
                        <input 
                            required
                            minlength="1"
                            type="password" 
                            name="confirm_new_password" 
                            id="confirm_new_password" 
                            match="new_password"
                            autocomplete="off"
                        />
                        <span class="error"></span>
                        <span class="error-icon"></span>
                        <span class="success-icon"></span>
                    </div>
                    HTML;
                    echo $endingPart;

                    echo <<<'EOD'
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
                            console.log(formGroup);
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
                    validateForm('#forgot_pass', sendToServer);
                    </script>
                    EOD;

                } else { // failed to answer question
                    echo <<<HTML
                    <div class="field input">
                        <label for="security_answer">$myQuestion</label>
                        <input 
                            required
                            type="text" 
                            name="security_answer" 
                            id="security_answer" 
                            autocomplete="off"
                        />
                    </div>
                    <div class='message'>
                        <p>Wrong Answer</p>
                    </div>
                    HTML;
                    echo $endingPart;

                }
            }  else if (isset($_POST['new_password']) && isset($_POST['confirm_new_password']) 
                        && $_SESSION['recovery_step']==2){
                $_SESSION['recovery_step']="";
                $_SESSION['recovery_user_details']="";
                
                echo <<<'EOD'
                                <div class="success_message">
                                    PASSWORD HAS BEEN CHANGED
                                </div>
                                </form>
                                    <a href="login.php" id="backtologin">Back to login</a>
                                </div>
                            </div>
                        </body>
                    </html>
                EOD;
            } else /* if (empty($_POST['username']) && $_SESSION['recovery_step']==0)*/ { //initial step
                echo <<<HTML
                    <div class="field input">
                        <label for="username">Username</label>
                        <input 
                            required
                            type="text" 
                            name="username" 
                            id="username" 
                            autocomplete="off"
                        />
                    </div>
                HTML;
                echo $endingPart;

            }
            ?>
