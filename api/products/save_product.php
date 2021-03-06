<?php
session_start();

require_once '../config.php';

$errors = array();
$data = array();
if(isset($_POST['product_id']) && !empty($_POST['product_id'])){
	$productId = $_POST['product_id'];
} else {
	$data['success'] = false;
	$data['message'] = 'Problem occurred';
	echo json_encode($data);
	die();
}
if (isset(
	$_POST['product_name'],
	$_POST['product_price'],
	$_POST['product_description'],
	$_POST['product_quantity'])) {

	$name = test_input($_POST['product_name']);
	$price = test_input($_POST['product_price']);
	$description = test_input($_POST['product_description']);
	$quantity = test_input($_POST['product_quantity']);
	$image = test_input($_POST['product_image']) ? test_input($_POST['product_image']) : '/assets/img/placeholder.png';
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

$query = 'update products
		set
			product_name = :productName,
			product_price = :price,
			product_description = :description,
			product_image = :image,
			product_quantity = :quantity
		where product_id = :productId';

$stmt = $dbh->prepare($query);
$stmt->bindValue(':productName', $name);
$stmt->bindValue(':price', $price);
$stmt->bindValue(':description', $description);
$stmt->bindValue(':image', $image);
$stmt->bindValue(':quantity', $quantity);
$stmt->bindValue(':productId', $productId);
$stmt->execute();

if($stmt->rowCount() == 1){
	$data['success'] = true;
	$data['message'] = 'Product information updated';

	generateJSON($dbh);
} else {
	$data['success'] = false;
	$data['message'] = 'Product save failed, please try again';
}

function generateJSON($dbh){
	$query = 'select product_id, product_name, product_description, product_image, product_price from products';

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	$sXml = '<?xml version="1.0" encoding="UTF-8" ?>
				<products>';

	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$toJSON[] = $row;

		$sId = $row['product_id'];
		$sName = $row['product_name'];
		$sPrice = $row['product_price'];
		$sDesc = $row['product_description'];
		$sPathToImage = $row['product_image'];
		$sXml .= "<product>
			<id>".$sId."</id>
			<name>".$sName."</name>
			<price>".$sPrice."</price>
			<price>".$sDesc."</price>
			<image>".$sPathToImage."</image>
		</product>";
	}

	$sXml .= "</products>";

	// save xml
	file_put_contents("../../products.xml", $sXml);

	$toReturn = array('products'=>$toJSON);

	// save json
	$fp = fopen('../../products.json', 'w+');
	fwrite($fp, json_encode($toReturn));
	fclose($fp);
}

echo json_encode($data);
