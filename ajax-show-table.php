<?php
function removequote($input)
{
	return str_replace(array('"', "'", "`"), "", $input);
}
if(isset($_POST))
{
	foreach($_POST as $key=>$val)
	{
		$_POST[$key] = removequote($val);
	}
}

if(isset($_POST['db1']) && isset($_POST['db2']))
{
	$host1 = (strlen(@$_POST['host1']))?(trim($_POST['host1'])):'localhost';
	$db1 = trim(@$_POST['db1']);
	$user1 = (strlen(@$_POST['user1']))?(trim($_POST['user1'])):'root';
	$pass1 = (strlen(@$_POST['pass1']))?(trim($_POST['pass1'])):'';
	
	$host2 = (strlen(@$_POST['host2']))?(trim($_POST['host2'])):'localhost';
	$db2 = trim(@$_POST['db2']);
	$user2 = (strlen(@$_POST['user2']))?(trim($_POST['user2'])):'root';
	$pass2 = (strlen(@$_POST['pass2']))?(trim($_POST['pass2'])):'';
		
	$connection1 = @mysql_connect($host1, $user1, $pass1);
	$connection2 = @mysql_connect($host2, $user2, $pass2);
	

	if(!$connection1 || !$connection2)
	{
		if(!$connection1) $s1 = false; else $s1 = true;
		if(!$connection2) $s2 = false; else $s2 = true;
		$output = array(
		"db1"=>array("connect"=>$s1, "selectdb"=>$s1, "data"=>null), 
		"db2"=>array("connect"=>$s2, "selectdb"=>$s2, "data"=>null), 
		'diftbl'=>null
		);
		echo json_encode($output);
		exit();
	}
	
	// test select db
	$sdb1 = @mysql_select_db($db1, $connection1);
	$sdb2 = @mysql_select_db($db2, $connection2);
	if(!$sdb1 || !$sdb2)
	{
		if(!$sdb1) $s1 = false; else $s1 = true;
		if(!$sdb2) $s2 = false; else $s2 = true;
		$output = array(
		"db1"=>array("connect"=>true, "selectdb"=>$s1, "data"=>null), 
		"db2"=>array("connect"=>true, "selectdb"=>$s2, "data"=>null), 
		'diftbl'=>null
		);
		echo json_encode($output);
		exit();
	}
	
	
	mysql_select_db($db1, $connection1);
	$sql1 = "show table status";
	$res1 = mysql_query($sql1, $connection1);
	$arr1 = array();	
	while(($dt = mysql_fetch_assoc($res1)))
	{
		$arr1[] = $dt;
	}
	
	mysql_select_db($db2, $connection2);
	$sql2 = "show table status";
	$res2 = mysql_query($sql2, $connection2);
	$arr2 = array();
	while(($dt = mysql_fetch_assoc($res2)))
	{
		$arr2[] = $dt;
	}
	
	// field list
	$fields = array();
	$fields['db1'] = array();
	$fields['db2'] = array();
	
	foreach($arr1 as $key=>$val)
	{
		$table = $val['Name'];
		$far = array();
		mysql_select_db($db1, $connection1);
		$sql = "SHOW COLUMNS FROM `$table`";
		$r = mysql_query($sql, $connection1);
		while(($dt = mysql_fetch_assoc($r)))
		{
			$far[] = $dt['Field'].'|'.$dt['Type'].'|'.$dt['Null'].'|'.$dt['Key'].'|'.$dt['Default'].'|'.$dt['Extra'];
		}
		$fields['db1'][$table] = implode(",", $far);
	}
	
	foreach($arr2 as $key=>$val)
	{
		$table = $val['Name'];
		$far = array();
		mysql_select_db($db2, $connection2);
		$sql = "SHOW COLUMNS FROM `$table`";
		$r = mysql_query($sql, $connection2);
		while(($dt = mysql_fetch_assoc($r)))
		{
			$far[] = $dt['Field'].'|'.$dt['Type'].'|'.$dt['Null'].'|'.$dt['Key'].'|'.$dt['Default'].'|'.$dt['Extra'];
		}
		$fields['db2'][$table] = implode(",", $far);
	}
	
	// list table which differ
	
	$diftbl = array();
	foreach($fields['db1'] as $key=>$val)
	{
		if(isset($fields['db2'][$key]))
		{
			if($val != $fields['db2'][$key])
			{
				$diftbl[] = $key;
			}
		}
	}
	foreach($fields['db2'] as $key=>$val)
	{
		if(isset($fields['db1'][$key]))
		{
			if($val != $fields['db1'][$key])
			{
				$diftbl[] = $key;
			}
		}
	}
	$diftbl = array_unique($diftbl);
	
	
	$output = array(
		"db1"=>array("connect"=>true, "selectdb"=>true, "data"=>$arr1), 
		"db2"=>array("connect"=>true, "selectdb"=>true, "data"=>$arr2), 
		'diftbl'=>$diftbl
		);
	echo json_encode($output);
}

?>