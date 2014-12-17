<?php
session_start();

require_once '../config.php';

$errors = array();
$data = array();

if (isset(
	$_POST['product_name'],
	$_POST['product_price'],
	$_POST['product_description'],
	$_POST['product_quantity'])) {

	$name = test_input($_POST['product_name']);
	$price = test_input($_POST['product_price']);
	$description = test_input($_POST['product_description']);
	$quantity = test_input($_POST['product_quantity']);
	$image = test_input($_POST['product_image']) ? test_input($_POST['product_image']) : 'http://placekitten.com/500/500';
}

function test_input($input = ''){

	$input = trim($input);
	$input = stripslashes($input);
	$input = htmlspecialchars($input);

	return $input;
}

try {
	$dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USERNAME, DB_PASSWORD);
} catch(PDOException $e) {
	echo $e->getMessage();
}

$query = 'insert into products (
		product_name,
		product_price,
		product_description,
		product_image,
		product_quantity )
	values (
		:name,
		:price,
		:description,
		:image,
		:quantity)';

$stmt = $dbh->prepare($query);
$stmt->bindValue(':name', $name);
$stmt->bindValue(':price', $price);
$stmt->bindValue(':description', $description);
$stmt->bindValue(':image', $image);
$stmt->bindValue(':quantity', $quantity);
$stmt->execute();

if($stmt->rowCount() == 1){
	$data['success'] = true;
	$data['message'] = 'Success!';

	generateJSON($dbh);
} else {
	$data['success'] = false;
	$data['message'] = 'Product adding failed, please try again';
}

function generateJSON($dbh){
	$query = 'select product_id, product_name, product_description, product_image, product_price from products';

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	$arr1 = array();
	while ($arr = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$toJSON[] = $arr;
	}

	$toReturn = array('products'=>$toJSON);

	$fp = fopen('../../products.json', 'w+');
	fwrite($fp, json_encode($toReturn));
	fclose($fp);
}

echo json_encode($data);