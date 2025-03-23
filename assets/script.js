let timeoutHandler;

document.addEventListener("DOMContentLoaded", function () {
    initEventListeners();
    if (window.localStorage) {
        loadSettings("dbdb1", "db1");
        loadSettings("dbdb2", "db2");
    }
});

function loadSettings(source, destination) {
    let data = JSON.parse(window.localStorage.getItem(source) || '{}');
    let parent = $(`.database-setting[data-db='${destination}']`);
    Object.keys(data).forEach(key => {
        let inputField = parent.find(`.${key}`);
        if (inputField.length) inputField.val(data[key]);
    });
}

function createTextarea() {
    return `<div class='textarea-wrapper'><textarea class='textarea' spellcheck='false'></textarea></div>`;
}

function initEventListeners() {
    $(document).on("click", ".link-create a", handleCreateTable);
    $(document).on("click", "#setting", function(e){
		showSettingForm(e);
	});
    $(document).on("click", function(e){
		handleSettingClick(e);
	});
    $(document).on("change", ".database-setting input", saveDatabaseSettings);
    $(document).on("click", ".swap-control", swapDatabaseSettings);
    $(document).on("click", "#list-tables", function(e){
		listTables(e);
	});
}

function showSettingForm(e)
{
	e.preventDefault();
	e.stopPropagation();
	$(".setting-form").css('display', 'block');
}

function handleCreateTable(e) {
    e.preventDefault();
    let dbSelector = $(this).closest(".missing-table2").length ? "2" : "1";
    let requestData = getDatabaseCredentials(dbSelector);
    requestData.table = $(this).attr("data-table");
    
    $.post("ajax-show-create-table.php", requestData, function (data) {
        showModal(".dialog-modal", {
            content: createTextarea(),
            title: `Create Table ${requestData.table}`,
            width: 360,
            buttons: { "Close": () => $(".dialog-modal").fadeOut(200) }
        });
        $("textarea.textarea").val(data["Create Table"]);
    }, "json").fail(console.log);
}

function getDatabaseCredentials(id) {
    return {
        host: $(`#host${id}`).val(),
        port: $(`#port${id}`).val(),
        db: $(`#db${id}`).val(),
        user: $(`#user${id}`).val(),
        pass: $(`#pass${id}`).val()
    };
}

function handleSettingClick(e) {
	clearTimeout(timeoutHandler);
	if(!e.target.closest('.setting-form'))
	{
		timeoutHandler = setTimeout(() => $(".setting-form").fadeOut(200), 100);
	}
}

function saveDatabaseSettings() {
    let dbRow = $(this).closest("tr");
    let dbName = dbRow.attr("data-db");
    let data = {};
    
    dbRow.find("input").each(function () {
        data[$(this).attr("class")] = $(this).val();
    });
    window.localStorage.setItem(`db${dbName}`, JSON.stringify(data));
}

function swapDatabaseSettings(e) {
    e.preventDefault();
    loadSettings("dbdb2", "db1");
    loadSettings("dbdb1", "db2");
    $(".database-setting input").change();
}

function listTables(e) {
	e.preventDefault();
	e.stopPropagation();
    let db1 = getDatabaseCredentials(1);
    let db2 = getDatabaseCredentials(2);
    console.log(db1);
	console.log(db2);
    if (!db1.db || !db2.db) {
        return showCustomAlert("Please complete setting.");
    }
    if (db1.host === db2.host && db1.port === db2.port && db1.db === db2.db) {
        return showCustomAlert("The databases must be different.");
    }
    let data = {
		'host1':db1.host,
		'port1':db1.port,
		'db1':db1.db,
		'user1':db1.user,
		'pass1':db1.pass,
		'host2':db2.host,
		'port2':db2.port,
		'db2':db2.db,
		'user2':db2.user,
		'pass2':db2.pass
		}
    $.post("ajax-show-table.php", data, function (data) {
        if (!data.db1.connect || !data.db2.connect) {
            return showCustomAlert("Cannot connect to one or both databases.");
        }
        if (!data.db1.selectdb || !data.db2.selectdb) {
            return showCustomAlert("Cannot use one or both databases.");
        }
        renderTableComparison(data, db1, db2);
    }, "json").fail(console.log);
}

function renderTableComparison(data, db1, db2) {
    let tables1 = renderTable(".table1-container", data.db1.data);
    let tables2 = renderTable(".table2-container", data.db2.data);
    
    markDifferences(tables1, tables2, "missing-table2");
    markDifferences(tables2, tables1, "missing-table1");
    
    showComparisonResult(tables1.length, tables2.length, data.diftbl.length, db1, db2);
}

function renderTable(container, data) {
    let tableHtml = `<table width='100%' border='1' class='table'>
        <thead>
            <tr><td>No</td><td>Name</td><td>Engine</td><td>Version</td><td>Row Format</td><td>Collation</td></tr>
        </thead>
        <tbody>
            ${data.map((row, i) => `<tr data-table='${row.Name}'>
                <td>${i + 1}</td>
                <td class='table-name'>${row.Name}</td>
                <td>${row.Engine}</td>
                <td>${row.Version}</td>
                <td>${row.Row_format}</td>
                <td>${row.Collation}</td>
            </tr>`).join("")}
        </tbody>
    </table>`;
    $(container).html(tableHtml);
    return data.map(row => row.Name);
}

function markDifferences(tables1, tables2, missingClass) {
    tables1.forEach(name => {
        if (!tables2.includes(name)) {
            $(`.table-container .table tbody tr[data-table='${name}']`).addClass(missingClass);
        }
    });
}

function showComparisonResult(miss1, miss2, diff, db1, db2) {
    let messages = [];
    if (miss1) messages.push(`Missing ${miss1} tables in ${db1.db}`);
    if (miss2) messages.push(`Missing ${miss2} tables in ${db2.db}`);
    if (diff) messages.push(`${diff} tables differ between ${db1.db} and ${db2.db}`);
    
    showModal(".dialog-modal", {
        content: messages.length ? messages.join("<br>") : "The database structure is identical.",
        title: messages.length ? "Table Differences" : "Congratulation",
        width: 360,
        buttons: { "Close": () => $(".dialog-modal").fadeOut(200) }
    });
}

function showCustomAlert(message) {
    showModal(".dialog-modal", {
        content: message,
        title: "Alert",
        width: 360,
        buttons: { "Close": () => $(".dialog-modal").fadeOut(200) }
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