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

if (isset($_POST['db1']) && isset($_POST['db2'])) {
	$host1 = (strlen(@$_POST['host1'])) ? (trim($_POST['host1'])) : 'localhost';
	$port1 = (strlen(@$_POST['port1'])) ? (trim($_POST['port1'])) : 3306;
	$db1 = trim(@$_POST['db1']);
	$user1 = (strlen(@$_POST['user1'])) ? (trim($_POST['user1'])) : 'root';
	$pass1 = (strlen(@$_POST['pass1'])) ? (trim($_POST['pass1'])) : '';

	$host2 = (strlen(@$_POST['host2'])) ? (trim($_POST['host2'])) : 'localhost';
	$port2 = (strlen(@$_POST['port2'])) ? (trim($_POST['port2'])) : 3306;
	$db2 = trim(@$_POST['db2']);
	$user2 = (strlen(@$_POST['user2'])) ? (trim($_POST['user2'])) : 'root';
	$pass2 = (strlen(@$_POST['pass2'])) ? (trim($_POST['pass2'])) : '';


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



	$sql1 = "show table status";
	try {
		$ldb_rs = $database1->prepare($sql1);
		$ldb_rs->execute();
		if ($ldb_rs->rowCount()) {
			$arr1 = $ldb_rs->fetchAll(PDO::FETCH_ASSOC);
		}
	} catch (Exception $e) {
		// do nothing
	}

	$sql2 = "show table status";
	try {
		$ldb_rs = $database2->prepare($sql2);
		$ldb_rs->execute();
		if ($ldb_rs->rowCount()) {
			$arr2 = $ldb_rs->fetchAll(PDO::FETCH_ASSOC);
		}
	} catch (Exception $e) {
		// do nothing
	}



	// field list
	$fields = array();
	$fields['db1'] = array();
	$fields['db2'] = array();

	foreach ($arr1 as $key => $val) {
		$table = $val['Name'];
		$far = array();
		$sql = "SHOW COLUMNS FROM `$table`";

		try {
			$ldb_rs = $database1->prepare($sql);
			$ldb_rs->execute();
			if ($ldb_rs->rowCount()) {
				$result = $ldb_rs->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $dt) {
					$far[] = $dt['Field'] . '|' . $dt['Type'] . '|' . $dt['Null'] . '|' . $dt['Key'] . '|' . $dt['Default'] . '|' . $dt['Extra'];
				}
			}
		} catch (Exception $e) {
			// do nothing
		}
		$fields['db1'][$table] = implode(",", $far);
	}

	foreach ($arr2 as $key => $val) {
		$table = $val['Name'];
		$far = array();
		$sql = "SHOW COLUMNS FROM `$table`";

		try {
			$ldb_rs = $database2->prepare($sql);
			$ldb_rs->execute();
			if ($ldb_rs->rowCount()) {
				$result = $ldb_rs->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $dt) {
					$far[] = $dt['Field'] . '|' . $dt['Type'] . '|' . $dt['Null'] . '|' . $dt['Key'] . '|' . $dt['Default'] . '|' . $dt['Extra'];
				}
			}
		} catch (Exception $e) {
			// do nothing
		}
		$fields['db2'][$table] = implode(",", $far);
	}


	// list table which differ

	$diftbl = array();
	foreach ($fields['db1'] as $key => $val) {
		if (isset($fields['db2'][$key])) {
			if ($val != $fields['db2'][$key]) {
				$diftbl[] = $key;
			}
		}
	}
	foreach ($fields['db2'] as $key => $val) {
		if (isset($fields['db1'][$key])) {
			if ($val != $fields['db1'][$key]) {
				$diftbl[] = $key;
			}
		}
	}
	$diftbl = array_unique($diftbl);


	$output = array(
		"db1" => array("connect" => true, "selectdb" => true, "data" => $arr1),
		"db2" => array("connect" => true, "selectdb" => true, "data" => $arr2),
		'diftbl' => $diftbl
	);
	echo json_encode($output);
}
