<?php
function removequote($input)
{
	return str_replace(array('"', "'", "`"), "", $input);
}
/**
 * Fungsi pembantu untuk mengambil data POST dengan nilai default
 * Kompatibel dengan PHP 5.2 ke atas
 */
function get_post($key, $default = '') {
    return (isset($_POST[$key]) && strlen(trim($_POST[$key])) > 0)
        ? trim($_POST[$key])
        : $default;
}
if (isset($_POST)) {
	foreach ($_POST as $key => $val) {
		$_POST[$key] = removequote($val);
	}
}

if (isset($_POST['db'])) {

	// Konfigurasi Database 1
	$host = get_post('host', 'localhost');
	$port = get_post('port', 3306);
	$db   = get_post('db', '');
	$user = get_post('user', 'root');
	$pass = get_post('pass', '');
	$table = get_post('table', '');



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
