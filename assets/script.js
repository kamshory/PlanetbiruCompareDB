let to = setTimeout(function(){}, 1);
function putSetting(source, destination)
{
	let db2 = window.localStorage.getItem(source) || '';
	let data = JSON.parse(db2);
	let key;
	let parent = $('.database-setting[data-db="'+destination+'"]');
	for(key in data)
	{
		if(parent.find("."+key).length)
		{
			parent.find("."+key).val(data[key]);
		}
	}
}

function createTextarea()
{
	let div = document.createElement('div');
	let textarea = document.createElement('textarea');
	div.classList.add('textarea-wrapper');
	textarea.classList.add('textarea');
	textarea.setAttribute('spellcheck', 'false');
	div.appendChild(textarea);
	return div.outerHTML;
}
window.onload = function(){
	
	$(document).on('click', '.link-create a', function(e){
		
		let host = $('#host1').val();
		let port = $('#port1').val();
		let db = $('#db1').val();
		let user = $('#user1').val();
		let pass = $('#pass1').val();
		
		
		e.preventDefault();
		let table = $(this).attr('data-table');
		console.log('ink-create', table);
		$.ajax({
			'type':'POST',
			'url':'ajax-show-create-table.php',
			'dataType':'json',
			'data':{
				'host':host,
				'db':db,
				'user':user,
				'pass':pass,
				'table':table
				},
			'success':function(data){
				console.log(data)
				
				showModal('.dialog-modal', {
					content:createTextarea(),
					title:'Create Table ' + table,
					width:360,
					buttons:{
						"Close":function(){
							$('.dialog-modal').fadeOut(200);
						}
					}
				});
				$('textarea.textarea').val(data['Create Table']);
			},
			'error':function(err){
				console.log(err)
			}
		});
	});
	
	$(document).on('click', '.missing-table2 .link-create a', function(e){
		
		let host = $('#host2').val();
		let port = $('#port2').val();
		let db = $('#db2').val();
		let user = $('#user2').val();
		let pass = $('#pass2').val();
		
		e.preventDefault();
		let table = $(this).attr('data-table');
		console.log('ink-create', table);
		$.ajax({
			'type':'POST',
			'url':'ajax-show-create-table.php',
			'dataType':'json',
			'data':{
				'host':host,
				'db':db,
				'user':user,
				'table':table
				},
			'success':function(data){
				console.log(data)
				
				showModal('.dialog-modal', {
					content: createTextarea(data['Create Table']),
					title:'Create Table ' + table,
					width:360,
					buttons:{
						"Close":function(){
							$('.dialog-modal').fadeOut(200);
						}
					}
				});
				$('textarea.textarea').val(data['Create Table']);
			},
			'error':function(err){
				console.log(err)
			}
		});
	});
	
	
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
	
	$(document).on('change', '.database-setting input', function(e){
		let dbrow = $(this).closest('tr');
		let dbnam = dbrow.attr('data-db');
		let dbinp = dbrow.find('input');
		let data = [];
		dbinp.each(function(index, element) {
            let inpname = $(this).attr('class');
            let inpvalue = $(this).val();
			dbrow[inpname] = inpvalue;
        });
		window.localStorage.setItem('db'+dbnam, JSON.stringify(dbrow));
	});

	if(window.localStorage){
		putSetting('dbdb1', 'db1');
		putSetting('dbdb2', 'db2');
	}
	$(document).on('click', '.swap-control', function(e){
		putSetting('dbdb2', 'db1');
		putSetting('dbdb1', 'db2');
		$('.database-setting input').change();
		e.preventDefault();
	});
	$(document).on('click', '#list-tables', function(){
		let host1 = $('#host1').val();
		let port1 = $('#port1').val();
		let db1 = $('#db1').val();
		let user1 = $('#user1').val();
		let pass1 = $('#pass1').val();
		
		let host2 = $('#host2').val();
		let port2 = $('#port2').val();
		let db2 = $('#db2').val();
		let user2 = $('#user2').val();
		let pass2 = $('#pass2').val();
		
		if(db1 == '' || db2 == '')
		{
			customAlert('.dialog-modal', 'Alert', 'Please complete setting.');
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
		else if(host1 == host2 && port1 == port2 && db1 == db2)
		{
			customAlert('.dialog-modal', 'Alert', 'The database to be compared must be diferent.');
			$('.setting-form').fadeIn(200);
		}
		else
		{
			$('.setting-form').fadeOut(200);
			$('#text_db1_name').text('('+host1+":"+port1+"/"+db1+')');
			$('#text_db2_name').text('('+host2+":"+port2+"/"+db2+')');
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
					let nmiss1 = 0;
					let ndiff1 = 0;
					let nmiss2 = 0;
					let ndiff2 = 0;
					if(!data.db1.connect || !data.db2.connect)
					{
						if(!data.db1.connect && !data.db2.connect)
						{
							if(host1 == '' || host2 == '')
							{
								customAlert('.dialog-modal', 'Alert', 'Can not connect to HOST');
							}
							else
							{
								customAlert('.dialog-modal', 'Alert', 'Can not connect to <strong>'+host1+'</strong> and host <strong>'+host2+'</strong>.');
							}
							$('#setting').click();
						}
						else if(!data.db1.connect)
						{
							if(host1 == '')
							{
								customAlert('.dialog-modal', 'Alert', 'Can not connect to HOST');
							}
							else
							{
								customAlert('.dialog-modal', 'Alert', 'Can not connect to <strong>'+host1+'</strong>.');
							}
							$('#setting').click();
						}
						else if(!data.db2.connect)
						{
							if(host2 == '')
							{
								customAlert('.dialog-modal', 'Alert', 'Can not connect to HOST');
							}
							else
							{
								customAlert('.dialog-modal', 'Alert', 'Can not connect to <strong>'+host2+'</strong>.');
							}
							$('#setting').click();
						}
					}
					else if(!data.db1.selectdb || !data.db2.selectdb)
					{
						if(!data.db1.selectdb && !data.db2.selectdb)
						{
							customAlert('.dialog-modal', 'Alert', 'Can not use database <strong>'+host1+":"+port1+"/"+db1+'</strong> and <strong>'+host2+":"+port2+"/"+db2+'</strong>.');
						}
						else if(!data.db1.selectdb)
						{
							customAlert('.dialog-modal', 'Alert', 'Can not use database <strong>'+host1+":"+port1+"/"+db1+'</strong>.');
						}
						else if(!data.db2.selectdb)
						{
							customAlert('.dialog-modal', 'Alert', 'Can not use database <strong>'+host2+":"+port2+"/"+db2+'</strong>.');
						}
						$('#setting').click();
					}
					else
					{
						let i, j, k, no;
						let html1 = '', html2 = '';
						let tables1 = [];
						let tables2 = [];
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
							'<td><a href="#" data-table="'+j.Name+'" class="table-name">'+j.Name+'</a></td>\r\n'+
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
								nmiss2++;
							}
						}
						for(i in tables2)
						{
							j = tables2[i];
							if($.inArray(j, tables1) == -1)
							{
								$('.table2-container .table tbody tr[data-table="'+j+'"]').addClass('missing-table2');
								nmiss1++;
							}
						}
						
						// marking diff table
						for(i in data.diftbl)
						{
							let j = data.diftbl[i];
							$('  <span class="asterisk">*</span>').insertAfter('.table-container .table tbody tr[data-table="'+j+'"] > td:nth-child(2) > a:nth-child(1)');
							ndiff1++;
							ndiff2 = ndiff1;
						}
						let message1 = "Missing "+nmiss1+" table in <strong>"+host1+":"+port1+"/"+db1+"</strong>";
						let message2 = "Missing "+nmiss2+" table in <strong>"+host2+":"+port2+"/"+db2+"</strong>";
						let message3 = ""+ndiff1+" diferent table between <strong>"+host1+":"+port1+"/"+db1+"</strong> and <strong>"+host2+":"+port2+"/"+db2+"</strong>";
						if(nmiss1 > 0 || nmiss2 > 0 || ndiff1 > 0)
						{
							let message = [];
							if(nmiss1 > 0)
							{
								message.push(message1);
								let table = $('.table2-container table tr.missing-table2').attr('data-table');
								showField(table);
							}
							if(nmiss2 > 0)
							{
								message.push(message2);
								let table = $('.table1-container table tr.missing-table1').attr('data-table');
								showField(table);
							}
							if(ndiff1 > 0)
							{
								message.push(message3);
							}
							showModal('.dialog-modal', {
								content:message.join('<br >'),
								title:'Table Difference',
								width:360,
								buttons:{
									"Close":function(){
										$('.dialog-modal').fadeOut(200);
									}
								}
							});
						}
						else
						{
							showModal('.dialog-modal', {
								content:'The database structure are identical.',
								title:'Congratulation',
								width:360,
								buttons:{
									"Close":function(){
										$('.dialog-modal').fadeOut(200);
									}
								}
							});
						}
					}
				}
			});
		}
		return false;	
	});
	$(document).on('click', '.table-container table tbody tr td a', function(){
		let tb = $(this).attr('data-table');
		showField(tb);
		return false;	
	});
};
function showField(tb)
{
	let host1 = $('#host1').val();
	let port1 = $('#port1').val();
	let db1 = $('#db1').val();
	let user1 = $('#user1').val();
	let pass1 = $('#pass1').val();
	let host2 = $('#host2').val();
	let port2 = $('#port2').val();
	let db2 = $('#db2').val();
	let user2 = $('#user2').val();
	let pass2 = $('#pass2').val();
	if(db1 == '' || db2 == '')
	{
		customAlert('.dialog-modal', 'Alert', 'Please complete setting.');
		$('.setting-form').fadeIn(200, 'swing', function(){
			if(db1 == '')
			{
				$('#db1').select();
			}
			else if(db2 == '')
			{
				$('#db2').select();
			}
		});
	}
	else if(host1 == host2 && port1 == port2 && db1 == db2)
	{
		customAlert('.dialog-modal', 'Alert', 'The database to be compared must be diferent.');
		$('.setting-form').fadeIn(200);
	}
	else
	{
		$.ajax({
		'type':'POST',
		'url':'ajax-show-field.php',
		'dataType':'json',
		'data':{
			'host1':host1,
			'port1':port1,
			'db1':db1,
			'user1':user1,
			'pass1':pass1,
			'host2':host2,
			'port2':port2,
			'db2':db2,
			'user2':user2,
			'pass2':pass2,
			'tb':tb
			},
		'success':function(data){
			if(!data.tb1.connect || !data.tb2.connect)
			{
				if(!data.tb1.connect && !data.tb2.connect)
				{
					customAlert('.dialog-modal', 'Alert', 'Can not connect to '+host1+':'+port1+' and host <strong>'+host2+':'+port2+'</strong>.');
				}
				else if(!data.tb1.connect)
				{
					customAlert('.dialog-modal', 'Alert', 'Can not connect to <strong>'+host1+':'+port1+'</strong>.');
				}
				else if(!data.tb2.connect)
				{
					customAlert('.dialog-modal', 'Alert', 'Can not connect to <strong>'+host2+':'+port2+'</strong>.');
				}
			}
			else if(!data.tb1.selectdb || !data.tb2.selectdb)
			{
				if(!data.tb1.selectdb && !data.tb2.selectdb)
				{
					customAlert('.dialog-modal', 'Alert', 'Can not use database <strong>'+host1+":"+port1+"/"+db1+'</strong> and <strong>'+host2+":"+port2+"/"+db2+'</strong>.');
				}
				else if(!data.tb1.selectdb)
				{
					customAlert('.dialog-modal', 'Alert', 'Can not use database <strong>'+host1+":"+port1+"/"+db1+'</strong>.');
				}
				else if(!data.tb2.selectdb)
				{
					customAlert('.dialog-modal', 'Alert', 'Can not use database <strong>'+host2+":"+port2+"/"+db2+'</strong>.');
				}
			}
			else
			{
				let i, j, k, l, no;
				// table 1
				let html1 = '<h3>Table 1: <span class="table-name">'+data.tb1.name+'</span></h3>';
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
				let html2 = '<h3>Table 2: <span class="table-name">'+data.tb2.name+'</span></h3>';
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
				let far1 ={}, fa1 = [];
				for(i in data.tb1.coldata)
				{
					let f = $.map(data.tb1.coldata[i], function(el) { return el; });
					far1[i] = f.join('|');
					fa1.push(f.join('|'));
				}
				
				let far2 = {}, fa2 = [];
				for(i in data.tb2.coldata)
				{
					let f = $.map(data.tb2.coldata[i], function(el) { return el; });
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
}
function customAlert(selector, title, content)
{
	showModal(selector, {
		title:title, 
		content:content, 
		buttons:{
			'Close':function(){
				$('.dialog-modal').fadeOut(200);
			}
		}
	});
}
function showModal(selector, options)
{
	$(selector).empty().append('<div class="dialog-modal-header"><h3></h3></div><div class="dialog-modal-body"></div><div class="dialog-modal-footer"></div>');
	options = options || {};
	let content = options.content || '';
	let title = options.title || '';
	let buttons = options.buttons || {};
	let i, caption;
	if(typeof buttons == 'object')
	{
		for(i in buttons)
		{
			caption = i;
			let btn = document.createElement('button');
			btn.innerText = caption;
			btn.addEventListener('click', buttons[i]);
			$(selector).find('.dialog-modal-footer')[0].appendChild(btn);
		}
	}
	
	$(selector).find('.dialog-modal-body').html(content);
	$(selector).find('.dialog-modal-header h3').text(title);
	let dw = options.width || 400;
	$(selector).css({
		width:dw
	})
	let ww = $(window).width();
	let wh = $(window).height();
	let dh = $(selector).height();
	let dl = (ww - dw) / 2;
	let dt = (wh - dh) / 2;
	$(selector).css({
		left:dl+'px',
		top:dt+'px',
	});
	$(selector).fadeIn(200);
}