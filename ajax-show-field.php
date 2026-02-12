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

if (isset($_POST['db1']) && isset($_POST['db2']) && isset($_POST['tb'])) {


// Konfigurasi Database 1
$host1 = get_post('host1', 'localhost');
$port1 = get_post('port1', 3306);
$db1   = get_post('db1', '');
$user1 = get_post('user1', 'root');
$pass1 = get_post('pass1', '');

// Konfigurasi Database 2
$host2 = get_post('host2', 'localhost');
$port2 = get_post('port2', 3306);
$db2   = get_post('db2', '');
$user2 = get_post('user2', 'root');
$pass2 = get_post('pass2', '');

$table = get_post('tb', '');
	// test select db

	try {

		$tz = date('P');
		$database1 = new PDO("mysql:host=" . $host1 . "; port=" . $port1 . "; dbname=" . $db1, $user1, $pass1);
		$database1->exec("SET time_zone='$tz'");
		$sdb1 = true;
	} catch (PDOException $e) {
		// do nothing
	}

	try {
		$tz = date('P');
		$database2 = new PDO("mysql:host=" . $host2 . "; port=" . $port2 . "; dbname=" . $db2, $user2, $pass2);
		$database2->exec("SET time_zone='$tz'");
		$sdb2 = true;
	} catch (PDOException $e) {
		// do nothing
	}

	if (!$sdb1 || !$sdb2) {
		if (!$sdb1) {
			$s1 = false;
		} else {
			$s1 = true;
		}
		if (!$sdb2) {
			$s2 = false;
		} else {
			$s2 = true;
		}
		$output = array(
			"db1" => array("connect" => true, "selectdb" => $s1, "data" => null),
			"db2" => array("connect" => true, "selectdb" => $s2, "data" => null),
			'diftbl' => null
		);
		echo json_encode($output);
		exit();
	}


	// field list
	$fields = array();
	$fields['db1'] = array();
	$fields['db2'] = array();

	$tabledata['db1'] = array();
	$tabledata['db2'] = array();

	$sql1 = "SHOW COLUMNS FROM $table";
	try {
		$ldb_rs = $database1->prepare($sql1);
		$ldb_rs->execute();
		$arr1 = $ldb_rs->fetchAll(PDO::FETCH_ASSOC);
		foreach ($arr1 as $dt) {
			if (!isset($caption1)) {
				$caption1 = array_keys($dt);
			}
			$tabledata['db1'][$dt['Field']] = $dt;
		}
		$caption1 = array('Field', 'Type', 'Null', 'Key', 'Default', 'Extra');
	} catch (Exception $e) {
		// do nothing
	}

	$sql2 = "SHOW COLUMNS FROM $table";
	$caption2 = array('Field', 'Type', 'Null', 'Key', 'Default', 'Extra');
	try {
		$ldb_rs = $database2->prepare($sql2);
		$ldb_rs->execute();
		$arr1 = $ldb_rs->fetchAll(PDO::FETCH_ASSOC);
		foreach ($arr1 as $dt) {
			if (!isset($caption2)) {
				$caption2 = array_keys($dt);
			}
			$tabledata['db2'][$dt['Field']] = $dt;
		}
	} catch (Exception $e) {
		// do nothing
	}

	$data = array(
		'tb1' => array(
			'name' => $table,
			'colcaption' => $caption1,
			'coldata' => $tabledata['db1']
		),
		'tb2' => array(
			'name' => $table,
			'colcaption' => $caption2,
			'coldata' => $tabledata['db2']
		)
	);

	echo json_encode(
		$data
	);
}
