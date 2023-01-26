<?php
include("includes/includedFiles.php");
?>

<div class="entityInfo">
	<div class="centerSection">
		<div class="userInfo">
			<h1><?php echo $userLoggedIn->getFirstAndLastName(); ?></h1>
			<span class="profileLink">
				<img class="profilePic" role="link" tabindex="0" src="<?php echo $userLoggedIn->getProfilePic(); ?>" onclick="openPage('yourMusic.php')">
			</span>
		</div>
	</div>

	<div class="buttonItems">
		<button class="button gold" onclick="openPage('updateDetails.php')">USER DETAILS</button>
		<button class="button gold" onclick="logout()">LOGOUT</button>
	</div>
	
</div>