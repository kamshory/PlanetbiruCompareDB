<?php
function removequote($input)
{
	return str_replace(array('"', "'", "`"), "", $input);
}
if (isset($_POST)) {
	foreach ($_POST as $key => $val) {
		$_POST[$key] = removequote($val);
	}
}

if (isset($_POST['db'])) {
	$host = (strlen(@$_POST['host'])) ? (trim($_POST['host'])) : 'localhost';
	$port = (strlen(@$_POST['port'])) ? (trim($_POST['port'])) : 3306;
	$db = trim(@$_POST['db']);
	$user = (strlen(@$_POST['user'])) ? (trim($_POST['user'])) : 'root';
	$pass = (strlen(@$_POST['pass'])) ? (trim($_POST['pass'])) : '';
	$table = (strlen(@$_POST['table'])) ? (trim($_POST['table'])) : '';


	// test select db

	try {

		$tz = date('P');
		$database1 = new PDO("mysql:host=" . $host . "; port=" . $port . "; dbname=" . $db, $user, $pass);
		$database1->exec("SET time_zone='$tz'");
		$sdb1 = true;
	} catch (PDOException $e) {
		// do nothing
	}

	
    header("Content-type: application/json");
	$sql1 = "show create table $table ";
	try {
		$ldb_rs = $database1->prepare($sql1);
		$ldb_rs->execute();
		if ($ldb_rs->rowCount()) {
			$arr1 = $ldb_rs->fetch(PDO::FETCH_ASSOC);
            echo json_encode($arr1);
		}
	} catch (Exception $e) {
		// do nothing
	}

	
}
