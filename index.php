<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CompareDB version 1.1</title>
<script type="text/javascript" src="jquery.js"></script>
<style type="text/css">
body{
	margin:0;
	padding:0;
	font-family:Tahoma, Geneva, sans-serif;
	font-size:12px;
	color:#555555;
}
form{
	margin:0;
	padding:0;
}
input, select, button, textarea{
	margin:0;
}
.all{
}
.wrapper{
	padding:10px 10px 10px 10px;
}
.input-bar{
	padding:10px;
	margin-bottom:10px;
	border-bottom:1px solid #EEEEEE;
	background-color:#F9F9F9;
}
.input-bar a{
	color:#444444;
	text-decoration:none;
}
.input-bar a:hover{
	text-decoration:underline;
}

.setting-form{
	background-color:#FDFDFD;
	border:1px solid #F5F5F5;
	padding:10px;
	width:520px;
	position:absolute;
	display:none;
}
.setting-form table td{
	padding:2px 0px;
	white-space:nowrap;
}
.setting-form table td:nth-child(2),
.setting-form table td:nth-child(3),
.setting-form table td:nth-child(4),
.setting-form table td:nth-child(5)
{
	width:100px;
	padding-left:4px;
}
.setting-form input[type="text"], .setting-form input[type="password"]{
	border:1px solid #EEEEEE;
	background-color:#FFFFFF;
	color:#555555;
	padding:4px;
	width:100px;
	border-radius:2px;
	font-family:Tahoma, Geneva, sans-serif;
	font-size:12px;
	color:#555555;
	transition:color 0.5s, box-shadow 0.5s, border-color 0.5s;
}
.setting-form input[type="text"]:focus, .setting-form input[type="password"]:focus{
	border: 1px solid #A6D3E3;
	box-shadow: 0px 0px 2px #D1E2E9;
}

.input-bar input[type="submit"], .input-bar input[type="button"]{
	padding:3px 12px;
	border:1px solid #DDDDDD;
	background:#EEEEEE;
	font-family:Tahoma, Geneva, sans-serif;
	font-size:12px;
	color:#555555;
	height:26px;
	overflow:hidden;
}
.db-area{
	width:50%;
	-webkit-box-sizing:border-box;
	-moz-box-sizing:border-box;
	box-sizing:border-box;

}
.db1-area{
	float:left;
	padding-right:5px;
}
.db2-area{
	float:right;
	padding-left:5px;
}
.db-area h3{
	margin:0px 0px 5px 0px;
	padding:0px;
}
.text_db_name{
	font-weight:normal;
}
.table1-container{
	border:1px solid #EEEEEE;
	padding:10px;
}
.table2-container{
	border:1px solid #EEEEEE;
	padding:10px;
}
.table1-container,
.table2-container{
	height:200px;
	overflow:auto;
}

.table1-container a,
.table2-container a{
	color:#555555;
	text-decoration:none;
}
.table-container .missing-table1 td:nth-child(2) a, .table-container .missing-table2 td:nth-child(2) a{
	color:#FF0000;
	font-weight:bold;
}
.table-container .table tbody tr .asterisk{
	color:#FF0000;
}
.field-container{
	margin-top:10px;
}
.table{
	border-collapse:collapse;	
}
.table thead td{
	padding:4px 4px;
	font-weight:bold;
}
.table tbody td{
	padding:3px 4px;
}
.table tbody tr.dif-field td{
	background-color:#FF9;
}
.clear{
	clear:both;
}
.title{
	display:inline-block;
	padding:0px 8px;
}
.table-name{
	font-weight:normal;
}
</style>
<script type="text/javascript">
/*
Copyright Planetbiru Studio 2015
All rights reserved
http://www.planetbiru.net
*/
var to = setTimeout(function(){}, 1);
window.onload = function(){
	$(document).on('click', '#setting', function(){
		$('.setting-form').fadeIn(200);
		setTimeout(function(){
		clearTimeout(to);
		}, 50);
	});
	$(document).on('click', function(){
		to = setTimeout(function(){
			$('.setting-form').fadeOut(200);
		}, 100);
	});
	$(document).on('click', '.setting-form', function(){
		setTimeout(function(){
		clearTimeout(to);
		}, 50);
	});
	$(document).on('change', '#db1', function(){
		var db1 = $(this).val();
		if(window.localStorage)
		{
			window.localStorage.setItem('db1', db1);
		}
	});
	if(window.localStorage){
		var db1 = window.localStorage.getItem('db1') || '';
		$('#db1').val(db1);
	}
	if(window.localStorage){
		var db2 = window.localStorage.getItem('db2') || '';
		$('#db2').val(db2);
	}
	$(document).on('change', '#db2', function(){
		var db2 = $(this).val();
		if(window.localStorage)
		{
			window.localStorage.setItem('db2', db2);
		}
	});
	$(document).on('click', '#list-tables', function(){
		var host1 = $('#host1').val();
		var db1 = $('#db1').val();
		var user1 = $('#user1').val();
		var pass1 = $('#pass1').val();
		
		var host2 = $('#host2').val();
		var db2 = $('#db2').val();
		var user2 = $('#user2').val();
		var pass2 = $('#pass2').val();
		
		if(db1 == '' || db2 == '')
		{
			alert('Please complete setting.');
			$('.setting-form').fadeIn(200, 'swing', function(){
				if(db1 == '')
				{
					$('#db1').select();
				}
				else if(db1 == '')
				{
					$('#db1').select();
				}
			});
		}
		else if(host1 == host2 && db1 == db2)
		{
			alert('Database to be compared must diferent.');
			$('.setting-form').fadeIn(200);
		}
		else
		{
			$('.setting-form').fadeOut(200);
			$('#text_db1_name').text('('+db1+')');
			$('#text_db2_name').text('('+db2+')');
			$.ajax({
			'type':'POST',
			'url':'ajax-show-table.php',
			'dataType':'json',
			'data':{
				'host1':host1,
				'db1':db1,
				'user1':user1,
				'pass1':pass1,
				'host2':host2,
				'db2':db2,
				'user2':user2,
				'pass2':pass2
				},
			'success':function(data){
				if(data.db1.connect == false || data.db2.connect == false)
				{
					if(data.db1.connect == false && data.db2.connect == false)
					{
						if(host1 == '' || host2 == '')
						{
							alert('Can not connect to HOST');
						}
						else
						{
							alert('Can not connect to '+host1+' and host '+host2+'.');
						}
						$('#setting').click();
					}
					else if(data.db1.connect == false)
					{
						if(host1 == '')
						{
							alert('Can not connect to HOST');
						}
						else
						{
							alert('Can not connect to '+host1+'.');
						}
						$('#setting').click();
					}
					else if(data.db2.connect == false)
					{
						if(host2 == '')
						{
							alert('Can not connect to HOST');
						}
						else
						{
							alert('Can not connect to '+host2+'.');
						}
						$('#setting').click();
					}
				}
				else if(data.db1.selectdb == false || data.db2.selectdb == false)
				{
					if(data.db1.selectdb == false && data.db2.selectdb == false)
					{
						alert('Can not use database '+db1+' and '+db2+'.');
					}
					else if(data.db1.selectdb == false)
					{
						alert('Can not use database '+db1+'.');
					}
					else if(data.db2.selectdb == false)
					{
						alert('Can not use database '+db2+'.');
					}
					$('#setting').click();
				}
				else
				{
					var i, j, k, no;
					var html1 = '', html2 = '';
					var tables1 = [];
					var tables2 = [];
					$('.table1-container').html('<table width="100%" border="1" class="table">\r\n'+
					'<thead>\r\n'+
					'<tr><td width="20">No</td><td>Name</td><td>Engine</td><td>Version</td><td>Row Format</td><td>Collation </td>\r\n'+
					'</tr>\r\n'+
					'</thead>\r\n'+
					'<tbody>\r\n'+
					'</tbody>\r\n'+
					'</table>\r\n');
					no = 1;
					for(i in data.db1.data)
					{
						j = data.db1.data[i];
						$('.table1-container table tbody').append('<tr data-table="'+j.Name+'">\r\n'+
						'<td align="right"><a href="#" data-table="'+j.Name+'">'+no+'</a></td>\r\n'+
						'<td><a href="#" data-table="'+j.Name+'">'+j.Name+'</a></td>\r\n'+
						'<td><a href="#" data-table="'+j.Name+'">'+j.Engine+'</a></td>\r\n'+
						'<td><a href="#" data-table="'+j.Name+'">'+j.Version+'</a></td>\r\n'+
						'<td><a href="#" data-table="'+j.Name+'">'+j.Row_format+'</a></td>\r\n'+
						'<td><a href="#" data-table="'+j.Name+'">'+j.Collation+'</a></td>\r\n'+
						'</tr>\r\n');
						no++
						tables1.push(j.Name);						
					}
					no = 1;
					$('.table2-container').html('<table width="100%" border="1" class="table">\r\n'+
					'<thead>\r\n'+
					'<tr><td width="20">No</td><td>Name</td><td>Engine</td><td>Version</td><td>Row Format</td><td>Collation </td>\r\n'+
					'</tr>\r\n'+
					'</thead>\r\n'+
					'<tbody>\r\n'+
					'</tbody>\r\n'+
					'</table>\r\n');
					for(i in data.db2.data)
					{
						j = data.db2.data[i];
						$('.table2-container table tbody').append('<tr data-table="'+j.Name+'">\r\n'+
						'<td align="right"><a href="#" data-table="'+j.Name+'">'+no+'</a></td>\r\n'+
						'<td><a href="#" data-table="'+j.Name+'">'+j.Name+'</a></td>\r\n'+
						'<td><a href="#" data-table="'+j.Name+'">'+j.Engine+'</a></td>\r\n'+
						'<td><a href="#" data-table="'+j.Name+'">'+j.Version+'</a></td>\r\n'+
						'<td><a href="#" data-table="'+j.Name+'">'+j.Row_format+'</a></td>\r\n'+
						'<td><a href="#" data-table="'+j.Name+'">'+j.Collation+'</a></td>\r\n'+
						'</tr>\r\n');
						no++
						tables2.push(j.Name);						
						
					}
					
					// marking missing table
					for(i in tables1)
					{
						j = tables1[i];
						if($.inArray(j, tables2) == -1)
						{
							$('.table1-container .table tbody tr[data-table="'+j+'"]').addClass('missing-table1');
						}
					}
					for(i in tables2)
					{
						j = tables2[i];
						if($.inArray(j, tables1) == -1)
						{
							$('.table2-container .table tbody tr[data-table="'+j+'"]').addClass('missing-table2');
						}
					}
					
					// marking diff table
					for(i in data.diftbl)
					{
						var j = data.diftbl[i];
						$('.table-container .table tbody tr[data-table="'+j+'"] td:nth-child(2)').append(' <span class="asterisk">*</span>');
					}
					}
				}
			});
		}
		return false;	
	});
	$(document).on('click', '.table-container table tbody tr td a', function(){
		var host1 = $('#host1').val();
		var db1 = $('#db1').val();
		var user1 = $('#user1').val();
		var pass1 = $('#pass1').val();
		var host2 = $('#host2').val();
		var db2 = $('#db2').val();
		var user2 = $('#user2').val();
		var pass2 = $('#pass2').val();
		if(db1 == '' || db2 == '')
		{
			alert('Please complete setting.');
			$('.setting-form').fadeIn(200, 'swing', function(){
				if(db1 == '')
				{
					$('#db1').select();
				}
				else if(db1 == '')
				{
					$('#db1').select();
				}
			});
		}
		else if(host1 == host2 && db1 == db2)
		{
			alert('Database to be compared must diferent.');
			$('.setting-form').fadeIn(200);
		}
		else
		{
			var tb = $(this).attr('data-table');
			$.ajax({
			'type':'POST',
			'url':'ajax-show-field.php',
			'dataType':'json',
			'data':{
				'host1':host1,
				'db1':db1,
				'user1':user1,
				'pass1':pass1,
				'host2':host2,
				'db2':db2,
				'user2':user2,
				'pass2':pass2,
				'tb':tb
				},
			'success':function(data){
				if(data.tb1.connect == false || data.tb2.connect == false)
				{
					if(data.tb1.connect == false && data.tb2.connect == false)
					{
						alert('Can not connect to '+host1+' and host '+host2+'.');
					}
					else if(data.tb1.connect == false)
					{
						alert('Can not connect to '+host1+'.');
					}
					else if(data.tb2.connect == false)
					{
						alert('Can not connect to '+host2+'.');
					}
				}
				else if(data.tb1.selectdb == false || data.tb2.selectdb == false)
				{
					if(data.tb1.selectdb == false && data.tb2.selectdb == false)
					{
						alert('Can not use database '+db1+' and '+db2+'.');
					}
					else if(data.tb1.selectdb == false)
					{
						alert('Can not use database '+db1+'.');
					}
					else if(data.tb2.selectdb == false)
					{
						alert('Can not use database '+db2+'.');
					}
				}
				else
				{
					var i, j, k, l, no;
					// table 1
					var html1 = '<h3>Table 1: <span class="table-name">'+data.tb1.name+'</span></h3>';
					html1 += '<table width="100%" border="1" class="table">';
					html1 += '<thead><tr>';
					html1 += '<td width="20">No</td>';
					for(i in data.tb1.colcaption)
					{
						html1 += '<td>'+data.tb1.colcaption[i]+'</td>';
					}
					html1 += '</tr></thead>';
					html1 += '<tbody>';
					no = 1;
					for(i in data.tb1.coldata)
					{
						j = data.tb1.coldata[i];
						html1 += '<tr data-field="'+i+'">';
						html1 += '<td align="right">'+(no++)+'</td>';
						for(k in j)
						{
							l = j[k];
							if(l === null)
							l = '&nbsp;';
							html1 += '<td>'+l+'</td>';
						}
						html1 += '</tr>';
					}
					html1 += '</tbody>';
					html1 += '</table>';
					$('.field1-container').html(html1);
					if(data.tb1.coldata.length == 0)
					{
						$('.field1-container').html('Table <strong>'+data.tb1.name+'</strong> is missing.');
					}
					
					// table 2
					var html2 = '<h3>Table 2: <span class="table-name">'+data.tb2.name+'</span></h3>';
					html2 += '<table width="100%" border="1" class="table">';
					html2 += '<thead><tr>';
					html2 += '<td width="20">No</td>';
					for(i in data.tb2.colcaption)
					{
						html2 += '<td>'+data.tb2.colcaption[i]+'</td>';
					}
					html2 += '</tr></thead>';
					html2 += '<tbody>';
					no = 1;
					for(i in data.tb2.coldata)
					{
						j = data.tb2.coldata[i];
						html2 += '<tr data-field="'+i+'">';
						html2 += '<td align="right">'+(no++)+'</td>';
						for(k in j)
						{
							l = j[k];
							if(l === null)
							l = '&nbsp;';
							html2 += '<td>'+l+'</td>';
						}
						html2 += '</tr>';
					}
					html2 += '</tbody>';
					html2 += '</table>';
					$('.field2-container').html(html2);
					if(data.tb2.coldata.length == 0)
					{
						$('.field2-container').html('Table '+data.tb2.name+' is missing.');
					}
								
					// marking dif field
					var far1 ={}, fa1 = [];
					for(i in data.tb1.coldata)
					{
						var f = $.map(data.tb1.coldata[i], function(el) { return el; });
						far1[i] = f.join('|');
						fa1.push(f.join('|'));
					}
					
					var far2 = {}, fa2 = [];
					for(i in data.tb2.coldata)
					{
						var f = $.map(data.tb2.coldata[i], function(el) { return el; });
						far2[i] = f.join('|');
						fa2.push(f.join('|'));
					}
					for(i in fa1)
					{
						if($.inArray(fa1[i], fa2) == -1)
						{
							j = fa1[i].split('|')[0];
							$('.field-container table tr[data-field="'+j+'"]').addClass('dif-field');
						}
					}
					for(i in fa2)
					{
						if($.inArray(fa2[i], fa1) == -1)
						{
							j = fa2[i].split('|')[0];
							$('.field-container table tr[data-field="'+j+'"]').addClass('dif-field');
						}
					}
					}
				}
			});
		}
		return false;	
	});
};
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
            <td>Database Name</td>
            <td>Username</td>
            <td>Password</td>
          </tr>
          <tr>
            <td>Database 1</td>
            <td><input type="text" name="host1" id="host1" /></td>
            <td><input type="text" name="db1" id="db1" /></td>
            <td><input type="text" name="user1" id="user1" /></td>
            <td><input type="password" name="pass1" id="pass1" /></td>
          </tr>
          <tr>
            <td>Database 2</td>
            <td><input type="text" name="host2" id="host2" /></td>
            <td><input type="text" name="db2" id="db2" /></td>
            <td><input type="text" name="user2" id="user2" /></td>
            <td><input type="password" name="pass2" id="pass2" /></td>
          </tr>
        </table>
        </div>
        
	  <span class="title">CompareDB version 1.1 - Created by <a href="http://www.planetbiru.net" target="_blank">Planetbiru Studio</a></span>
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
</body>
</html>