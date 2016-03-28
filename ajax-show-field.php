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
if(isset($_POST['db1']) && isset($_POST['db2']) && isset($_POST['tb']))
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
		
	$table = trim(@$_POST['tb']);
	
	$connection1 = @mysql_connect($host1, $user1, $pass1);
	$connection2 = @mysql_connect($host2, $user2, $pass2);

	if(!$connection1 || !$connection2)
	{
		if(!$connection1) $s1 = false; else $s1 = true;
		if(!$connection2) $s2 = false; else $s2 = true;
		$output = array(
		"tb1"=>array("connect"=>$s1, "selectdb"=>$s1, "name"=>$table, "colcaption"=>null, "coldata"=>null), 
		"tb2"=>array("connect"=>$s2, "selectdb"=>$s2, "name"=>$table, "colcaption"=>null, "coldata"=>null)
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
		"tb1"=>array("connect"=>true, "selectdb"=>$s1, "name"=>$table, "colcaption"=>null, "coldata"=>null), 
		"tb2"=>array("connect"=>true, "selectdb"=>$s2, "name"=>$table, "colcaption"=>null, "coldata"=>null)
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
	
	mysql_select_db($db1, $connection1);
	$r1 = mysql_query("SHOW COLUMNS FROM $table", $connection1);
	if($r1)
	{
		$caption1 = null;
		while(($dt = mysql_fetch_assoc($r1)))
		{
			if(!isset($caption1))
			{
				$caption1 = array_keys($dt);
			}
			$tabledata['db1'][$dt['Field']] = $dt;
		}
	}
	else
	{
		$caption1 = array('Field', 'Type', 'Null', 'Key', 'Default', 'Extra');
	}
	mysql_select_db($db2, $connection2);
	$r2 = mysql_query("SHOW COLUMNS FROM $table", $connection2);
	$caption2 = null;
	if($r2)
	{
	while(($dt = mysql_fetch_assoc($r2)))
	{
		if(!isset($caption2))
		{
			$caption2 = array_keys($dt);
		}
		$tabledata['db2'][$dt['Field']] = $dt;
	}
	}
	else
	{
		$caption2 = array('Field', 'Type', 'Null', 'Key', 'Default', 'Extra');
	}
	echo json_encode(
		array(
			'tb1'=>array(
				'name'=>$table, 
				'colcaption'=>$caption1, 
				'coldata'=>$tabledata['db1']
				)
			,
			'tb2'=>array(
				'name'=>$table, 
				'colcaption'=>$caption2, 
				'coldata'=>$tabledata['db2']
				)
			)
	);
}

?>