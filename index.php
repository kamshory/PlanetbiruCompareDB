<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>CompareDB version 1.2</title>
<script type="text/javascript" src="jquery.js"></script>
<script src="assets/script.js"></script>
<link rel="stylesheet" href="assets/style.css">


<script type="text/javascript">
/*
Copyright Planetbiru Studio 2015
All rights reserved
http://www.planetbiru.com
*/

</script>
</head>

<body>
<div class="all">
	<div class="input-bar">
	  <form name="form1" method="post" action="">
		<input type="button" id="setting" value="&star;">
        <input type="submit" name="list-tables" id="list-tables" value="List Tables">
        <div class="setting-form">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td>&nbsp;</td>
            <td>Host</td>
            <td>Port</td>
            <td>Database Name</td>
            <td>Username</td>
            <td>Password</td>
            <td>&nbsp;</td>
          </tr>
          <tr data-db="db1" class="database-setting">
            <td>Database 1</td>
            <td><input class="input-host" type="text" name="host1" id="host1" /></td>
            <td><input class="input-port" type="number" name="port1" id="port1" /></td>
            <td><input class="input-db" type="text" name="db1" id="db1" /></td>
            <td><input class="input-user" type="text" name="user1" id="user1" /></td>
            <td><input class="input-pass" type="password" name="pass1" id="pass1" /></td>
            <td rowspan="2" align="center"><a href="#" class="swap-control"><span class="swap-icon"></span></a></td>
          </tr>
          <tr data-db="db2" class="database-setting">
            <td>Database 2</td>
            <td><input class="input-host" type="text" name="host2" id="host2" /></td>
            <td><input class="input-port" type="number" name="port2" id="port2" /></td>
            <td><input class="input-db" type="text" name="db2" id="db2" /></td>
            <td><input class="input-user" type="text" name="user2" id="user2" /></td>
            <td><input class="input-pass" type="password" name="pass2" id="pass2" /></td>
          </tr>
        </table>
        </div>
        
	  <span class="title">CompareDB version 1.2 - Created by <a href="https://www.planetbiru.com/" target="_blank">Planetbiru Studio</a></span>
	  </form>
    </div>

<div class="wrapper">    
    
    <div class="db-area db1-area">
    	<h3>Database 1: <span id="text_db1_name" class="text_db_name"></span></h3>
    	<div class="table-container table1-container"></div>
        <div class="field-container field1-container"></div>
    </div>
    <div class="db-area db2-area">
    	<h3>Database 2: <span id="text_db2_name" class="text_db_name"></span></h3>
    	<div class="table-container table2-container"></div>
        <div class="field-container field2-container"></div>
    </div>
    <div class="clear"></div>
</div>    
    
</div>
<script type="text/javascript">

</script>
<style type="text/css">

</style>
<div class="dialog-modal">
	
</div>
</body>
</html>