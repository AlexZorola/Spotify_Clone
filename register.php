<?php
	include("includes/config.php");
	include("includes/classes/Account.php");
	include("includes/classes/Constants.php");

	$account = new Account($con);

	include("includes/handlers/register-handler.php");
	include("includes/handlers/login-handler.php");

	function getInputValue($name){
		if(isset($_POST[$name])){
			echo $_POST[$name];
		}
	}
?>

<html>
<head>
	<title>On The Luxury's!</title>
	<link rel="stylesheet" type="text/css" href="assets/css/register.css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="assets/js/register.js"></script>
</head>
<body>

	<?php

		if(isset($_POST['registerButton'])){ //Determines which button is pressed
			echo '<script>
					$(document).ready(function(){
						$("#loginForm").hide();
						$("#registerForm").show();
					});
				</script>';
		}
		else{
			echo '<script>
					$(document).ready(function(){
						$("#loginForm").show();
						$("#registerForm").hide();
					});
				</script>';
		}

	?>

	<div id="background">
		<div id="loginContainer">
			<div id="inputContainer">
				<form id="loginForm" action="register.php" method="POST">
					<h2>Login to your account!</h2>
					<p>
						<?php echo $account->getError(Constants::$loginFailed); ?>
						<label for="loginUsername">Username:</label>
						<input id="loginUsername" name="loginUsername" type="text" placeholder="e.g. lovemusic" value="<?php getInputValue('loginUsername') ?>" required>
					</p>
					<p>
						<label for="loginPassword">Password:</label>
						<input id="loginPassword" name="loginPassword" type="password" placeholder="Your Password" required>
					</p>

					<button type="submit" name="loginButton">Log In</button>

					<div class="hasAccountText">
						<span id="hideLogin"> Don't have an account yet? Sign Up Here!</span>
					</div>
					
				</form>


				<form id="registerForm" action="register.php" method="POST">
					<h2>Create your free account!</h2>
					<p>
						<?php echo $account->getError(Constants::$usernameCharacters); ?>
						<?php echo $account->getError(Constants::$usernameTaken); ?>
						<label for="registerUsername">Username:</label>
						<input id="registerUsername" name="registerUsername" type="text" placeholder="e.g. lovemusic" value="<?php getInputValue('registerUsername'); ?>" required>
					</p>

					<p>
						<?php echo $account->getError(Constants::$firstNameCharacters); ?>
						<label for="firstName">First Name:</label>
						<input id="firstName" name="firstName" type="text" placeholder="e.g. Alex" value="<?php getInputValue('firstName'); ?>" required>
					</p>

					<p>
						<?php echo $account->getError(Constants::$lastNameCharacters); ?>
						<label for="lastName">Last Name:</label>
						<input id="lastName" name="lastName" type="text" placeholder="e.g. Zorola" value="<?php getInputValue('lastName'); ?>" required>
					</p>

					<p>
						<?php echo $account->getError(Constants::$emailsDoNotMatch); ?>
						<?php echo $account->getError(Constants::$emailInvalid); ?>
						<?php echo $account->getError(Constants::$emailTaken); ?>

						<label for="email">Email:</label>
						<input id="email" name="email" type="email" placeholder="e.g. lovemusic@gmail.com" value="<?php getInputValue('email'); ?>" required>
					</p>

					<p>
						<label for="email2">Confirm Email:</label>
						<input id="email2" name="email2" type="email" placeholder="e.g. lovemusic@gmail.com" value="<?php getInputValue('email2'); ?>" required>
					</p>

					<p>
						<?php echo $account->getError(Constants::$passwordsDoNotMatch); ?>
						<?php echo $account->getError(Constants::$passwordNotAlphanumeric); ?>
						<?php echo $account->getError(Constants::$passwordCharacters); ?>
						<label for="registerPassword">Password:</label>
						<input id="registerPassword" name="registerPassword" type="password" placeholder="Your Password" required>
					</p>

					<p>
						<label for="registerPassword2">Confirm Password:</label>
						<input id="registerPassword2" name="registerPassword2" type="password" placeholder="Confirm Password" required>
					</p>

					<button type="submit" name="registerButton">Sign Up</button>

					<div class="hasAccountText">
						<span id="hideRegister"> Already have an account? Login Here!</span>
					</div>
					
				</form>
			</div>
			<div id="loginText">
				<h1>On The Luxury's</h1>
				<h2>Listen to loads of songs for free!</h2>
				<ul>
					<li>Listen to your favorite artists or Discover new ones!</li>
					<li>Listen to Instrumentals/Beats and Download them!</li>
					<li>Create Your Own Playlists!</li>
				</ul>
			</div>
		</div>
	</div>

</body>
</html>