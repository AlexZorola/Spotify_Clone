<?php
	class Account{

		private $con;
		private $errorArray;

		public function __construct($con){
			$this->con = $con;
			$this->errorArray = array();
		}

		public function login($uN, $pw){
			$pw = md5($pw);

			$query = mysqli_query($this->con, "SELECT * FROM users WHERE username='$uN' AND password='$pw'");

			if(mysqli_num_rows($query) == 1){
				return true;
			}
			else{
				array_push($this->errorArray, Constants::$loginFailed);
				return false;
			}
		}

		public function register($uN, $fN, $lN, $em, $em2, $pw, $pw2){ //Manually call this and this function can call private functions
																		//because it's within the same class
			$this->validateUsername($uN);
			$this->validateFirstName($fN);
			$this->validateLastName($lN);
			$this->validateEmails($em, $em2);
			$this->validatePasswords($pw, $pw2);

			if(empty($this->errorArray == true)){
				//Insert into DataBase
				return $this->insertUserDetails($uN, $fN, $lN, $em, $pw);
			}
			else{
				return false;
			}
		}

		public function getError($error){
			if(!in_array($error, $this->errorArray)){
				$error = "";
			}
			return "<span class='errorMessage'>$error</span>";
		}

		private function insertUserDetails($uN, $fN, $lN, $em, $pw){
			$encryptedPw = md5($pw); //Password -> vhrjkhgerjk6546546hfke
			$profilePic = "assets/images/profile-pics/awesome.png";
			$date = date("Y-m-d");

			$result = mysqli_query($this->con, "INSERT INTO users VALUES ('', '$uN', '$fN', '$lN', '$em', '$encryptedPw', '$date', '$profilePic')");

			return $result;
		}

		private function validateUsername($uN){
			
			if(strlen($uN) > 25 || strlen($uN) < 5){
				array_push($this->errorArray, Constants::$usernameCharacters);
				return;
			}

			$checkUsernameQuery = mysqli_query($this->con, "SELECT username FROM users WHERE username='$uN'");
			if(mysqli_num_rows($checkUsernameQuery) != 0){
				array_push($this->errorArray, Constants::$usernameTaken);
				return;
			}
		}

		private function validateFirstName($fN){
			
			if(strlen($fN) > 25 || strlen($fN) < 2){
				array_push($this->errorArray, Constants::$firstNameCharacters);
				return;
			}
		}

		private function validateLastName($lN){
			
			if(strlen($lN) > 25 || strlen($lN) < 2){
				array_push($this->errorArray, Constants::$lastNameCharacters);
				return;
			}
		}

		private function validateEmails($em, $em2){
			
			if($em != $em2){
				array_push($this->errorArray, Constants::$emailsDoNotMatch);
				return;
			}

			if(!filter_var($em, FILTER_VALIDATE_EMAIL)){
				array_push($this->errorArray, Constants::$emailInvalid);
				return;
			}

			$checkEmailQuery = mysqli_query($this->con, "SELECT email FROM users WHERE email='$em'");
			if(mysqli_num_rows($checkEmailQuery) != 0){
				array_push($this->errorArray, Constants::$emailTaken);
				return;
			}
		}

		private function validatePasswords($pw, $pw2){
			
			if($pw != $pw2){
				array_push($this->errorArray, Constants::$passwordsDoNotMatch);
				return;
			}

			if(preg_match('/[^A-Za-z0-9]/', $pw)){ //If it contains anything other than alphanumeric characters (decimals, exclamations etc...)
				array_push($this->errorArray, Constants::$passwordNotAlphanumeric);
				return;
			}

			if(strlen($pw) > 30 || strlen($pw) < 5){
				array_push($this->errorArray, Constants::$passwordCharacters);
				return;
			}
		}

	}
?>