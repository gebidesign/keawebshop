<?php

require_once '../config.php';

$errors = array();
$data = array();

$email = $password = '';

if(isset($_POST['email']) && isset($_POST['password'])){
	$email = test_input($_POST['email']);
	$password = test_input($_POST['password']);
}

function test_input($input = ''){

	$input = trim($input);
	$input = stripslashes($input);
	$input = htmlspecialchars($input);

	return $input;
}

// validate and set errors
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
	$errors['email'] = "Invalid email format";
}
if(empty($email)){
	$errors['email'] = 'Email is required';
}
if(strlen($password) < 6){
	$errors['password'] = 'Password needs to have at least 6 characters';
}
if(empty($password)){
	$errors['password'] = 'Password is required';
}

// store errors
if(!empty($errors)){

	$data['success'] = false;
	$data['errors'] = $errors;

} else {

	// unset errors
	unset($errors);

	try {
		$dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USERNAME, DB_PASSWORD);
	} catch(PDOException $e) {
		echo $e->getMessage();
	}

	$query = 'select customer_password from customers where customer_email=:customerEmail';
	$stmt = $dbh->prepare($query);
	$stmt->bindValue(':customerEmail', $email);
	$stmt->execute();

	if($stmt->rowCount() == 1){

		$hash = $stmt->fetchColumn();

		if (password_verify($password, $hash)) {
			$data['success'] = true;
			$data['message'] = 'Success!';
		} else {
			$data['success'] = false;
			$data['message'] = 'Password doesn\'t match, try again';
		}

	} else {
		$data['success'] = false;
		$data['message'] = 'Email not found, try again';
	}
}

// return errors json
echo json_encode($data);