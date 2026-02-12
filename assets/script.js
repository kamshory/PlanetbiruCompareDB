document.addEventListener("DOMContentLoaded", function() {
    
    // Utility function for AJAX POST requests
    function ajaxPost(url, data, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                callback(xhr.responseText);
            }
        };
        var params = [];
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                params.push(encodeURIComponent(key) + "=" + encodeURIComponent(data[key]));
            }
        }
        xhr.send(params.join("&"));
    }

    // Toggle Settings Visibility
    var settingButton = document.getElementById("setting");
    var settingForm = document.querySelector(".setting-form");
    if (settingButton && settingForm) {
        settingButton.addEventListener("click", function() {
            var display = settingForm.style.display || getComputedStyle(settingForm).display;
            if (display === "none") {
                settingForm.style.display = "block";
            } else {
                settingForm.style.display = "none";
            }
        });
    }

    // Swap Database Configuration
    var swapControl = document.querySelector(".swap-control");
    if (swapControl) {
        swapControl.addEventListener("click", function(e) {
            e.preventDefault();
            var fields = ["host", "port", "db", "user", "pass"];
            fields.forEach(function(field) {
                var el1 = document.getElementById(field + "1");
                var el2 = document.getElementById(field + "2");
                if (el1 && el2) {
                    var temp = el1.value;
                    el1.value = el2.value;
                    el2.value = temp;
                }
            });
        });
    }

    // List Tables Action
    var listTablesBtn = document.getElementById("list-tables");
    var form = document.forms["form1"];
    
    if (listTablesBtn && form) {
        listTablesBtn.addEventListener("click", function(e) {
            e.preventDefault();
            listTables();
        });
    }

    function getFormData() {
        var data = {};
        var elements = form.elements;
        for (var i = 0; i < elements.length; i++) {
            var el = elements[i];
            if (el.name && (el.type !== "submit" && el.type !== "button")) {
                data[el.name] = el.value;
            }
        }
        return data;
    }

    function listTables() {
        var data = getFormData();
        
        // Update database name headers
        var textDb1 = document.getElementById("text_db1_name");
        var textDb2 = document.getElementById("text_db2_name");
        if(textDb1) textDb1.innerText = data.db1;
        if(textDb2) textDb2.innerText = data.db2;

        ajaxPost("ajax-show-table.php", data, function(response) {
            if(!response) return;
            try {
                var res = JSON.parse(response);
                renderTableList(res);
            } catch (e) {
                console.error("Error parsing JSON response", e);
            }
        });
    }

    function renderTableList(res) {
        var container1 = document.querySelector(".table1-container");
        var container2 = document.querySelector(".table2-container");
        
        container1.innerHTML = "";
        container2.innerHTML = "";

        var diffs = res.diftbl || [];

        function createList(dbData, dbKey, container) {
            if (dbData && dbData.data) {
                var ul = document.createElement("ul");
                ul.className = "table-list";
                dbData.data.forEach(function(row) {
                    var li = document.createElement("li");
                    li.innerText = row.Name;
                    li.setAttribute("data-table", row.Name);
                    
                    if (diffs.indexOf(row.Name) !== -1) {
                        li.classList.add("diff");
                    }

                    // Click to load fields
                    li.addEventListener("click", function() {
                        var allLi = document.querySelectorAll(".table-container li");
                        for(var i=0; i<allLi.length; i++) allLi[i].classList.remove("active");
                        
                        li.classList.add("active");
                        var counterpart = (dbKey === "db1" ? container2 : container1).querySelector("li[data-table='" + row.Name + "']");
                        if(counterpart) counterpart.classList.add("active");

                        loadFields(row.Name);
                    });
                    
                    // Right click to show Create Table SQL
                    li.addEventListener("contextmenu", function(e) {
                        e.preventDefault();
                        showCreateTable(dbKey, row.Name);
                    });

                    ul.appendChild(li);
                });
                container.appendChild(ul);
            }
        }

        createList(res.db1, "db1", container1);
        createList(res.db2, "db2", container2);
    }

    function loadFields(tableName) {
        var data = getFormData();
        data.tb = tableName;

        ajaxPost("ajax-show-field.php", data, function(response) {
            if(!response) return;
            try {
                var res = JSON.parse(response);
                renderFields(res);
            } catch (e) {
                console.error("Error parsing JSON response", e);
            }
        });
    }

    function renderFields(res) {
        var container1 = document.querySelector(".field1-container");
        var container2 = document.querySelector(".field2-container");

        container1.innerHTML = createFieldTable(res.tb1);
        container2.innerHTML = createFieldTable(res.tb2);
        
        var hasDiff = highlightFieldDiffs();

        // Tambahkan tombol "Generate SQL"
        var actionContainer = document.getElementById("action-container");
        if (actionContainer) {
            var tableName = (res.tb1 && res.tb1.name) || (res.tb2 && res.tb2.name);
            if (tableName) {
                if (hasDiff) {
                    actionContainer.innerHTML = '<button id="generate-sql-btn">Generate Sync SQL for `' + tableName + '`</button>';
                    document.getElementById("generate-sql-btn").addEventListener("click", function() {
                        generateAlterSql(tableName);
                    });
                } else {
                    actionContainer.innerHTML = '<div class="message-identical">Tables are identical.</div>';
                }
            } else {
                actionContainer.innerHTML = "";
            }
        }
    }

    function clearActionContainer() {
        document.getElementById("action-container").innerHTML = "";
    }

    function createFieldTable(tbData) {
        if (!tbData || !tbData.coldata) return "";
        
        var html = "<table border='1' cellspacing='0' cellpadding='3' width='100%'>";
        html += "<thead><tr>";
        if (tbData.colcaption) {
            tbData.colcaption.forEach(function(cap) {
                html += "<th>" + cap + "</th>";
            });
        }
        html += "</tr></thead><tbody>";

        for (var fieldName in tbData.coldata) {
            if (tbData.coldata.hasOwnProperty(fieldName)) {
                var row = tbData.coldata[fieldName];
                html += "<tr data-field='" + fieldName + "'>";
                var keys = ["Field", "Type", "Null", "Key", "Default", "Extra"];
                keys.forEach(function(k) {
                    html += "<td>" + (row[k] !== null ? row[k] : "NULL") + "</td>";
                });
                html += "</tr>";
            }
        }
        html += "</tbody></table>";
        return html;
    }

    function highlightFieldDiffs() {
        var rows1 = document.querySelectorAll(".field1-container tbody tr");
        var rows2 = document.querySelectorAll(".field2-container tbody tr");
        
        var map1 = {};
        for(var i=0; i<rows1.length; i++) map1[rows1[i].getAttribute("data-field")] = rows1[i];
        
        var map2 = {};
        for(var i=0; i<rows2.length; i++) map2[rows2[i].getAttribute("data-field")] = rows2[i];

        var diffFound = false;

        for (var f in map1) {
            if (!map2[f]) {
                map1[f].classList.add("row-missing"); // Missing in DB2
                diffFound = true;
            } else {
                if (map1[f].innerHTML !== map2[f].innerHTML) {
                    map1[f].classList.add("row-diff"); // Different
                    map2[f].classList.add("row-diff");
                    diffFound = true;
                }
            }
        }
        for (var f in map2) {
            if (!map1[f]) {
                map2[f].classList.add("row-missing"); // Missing in DB1
                diffFound = true;
            }
        }
        return diffFound;
    }

    function generateAlterSql(tableName) {
        var data = getFormData();
        data.tb = tableName;

        var btn = document.getElementById("generate-sql-btn");
        if(btn) {
            btn.disabled = true;
            btn.textContent = "Generating...";
        }

        ajaxPost("ajax-generate-alter.php", data, function(response) {
            if(btn) {
                btn.disabled = false;
                btn.textContent = 'Generate Sync SQL for `' + tableName + '`';
            }
            
            try {
                var res = JSON.parse(response);
                if (res.error) {
                    alert("An error occurred: " + res.error);
                    return;
                }
                showAlterSqlModal(tableName, res);
            } catch (e) {
                console.error("Error parsing JSON for ALTER SQL", e);
                alert("Could not process the request to generate SQL.");
            }
        });
    }

    function showAlterSqlModal(tableName, sqlData) {
        var modal = document.querySelector(".dialog-modal");
        if (!modal) return;

        var sql1 = sqlData.to_sync_db1.join("\n") || "-- No changes needed --";
        var sql2 = sqlData.to_sync_db2.join("\n") || "-- No changes needed --";

        var db1Name = document.getElementById("db1").value;
        var db2Name = document.getElementById("db2").value;

        var modalContent = `
            <div class="modal-content modal-content-large">
                <button class="modal-close-btn" onclick="this.closest('.dialog-modal').style.display='none'">&times;</button>
                <h3>Synchronize SQL for Table \`${tableName}\`</h3>
                <div class="modal-flex-container">
                    <div class="modal-flex-item">
                        <h4>Make \`${db1Name}\` like \`${db2Name}\`</h4>
                        <p>Run this SQL on Database 1:</p>
                        <textarea class="modal-textarea" readonly>${sql1}</textarea>
                    </div>
                    <div class="modal-flex-item">
                        <h4>Make \`${db2Name}\` like \`${db1Name}\`</h4>
                        <p>Run this SQL on Database 2:</p>
                        <textarea class="modal-textarea" readonly>${sql2}</textarea>
                    </div>
                </div>
            </div>
        `;

        modal.innerHTML = modalContent;
        modal.style.display = "block";

        modal.onclick = function(e) {
            if (e.target === modal) modal.style.display = "none";
        };
    }

    function showCreateTable(dbKey, tableName) {
        var data = getFormData();
        data.db = data[dbKey];
        data.host = data["host" + (dbKey === "db1" ? "1" : "2")];
        data.port = data["port" + (dbKey === "db1" ? "1" : "2")];
        data.user = data["user" + (dbKey === "db1" ? "1" : "2")];
        data.pass = data["pass" + (dbKey === "db1" ? "1" : "2")];
        data.table = tableName;

        ajaxPost("ajax-show-create-table.php", data, function(response) {
            try {
                var res = JSON.parse(response);
                var sql = res["Create Table"];
                var modal = document.querySelector(".dialog-modal");
                if (modal) {
                    modal.innerHTML = "<div class='modal-content modal-content-medium'>" +
                        "<button class='modal-close-btn-simple' onclick='this.closest(\".dialog-modal\").style.display=\"none\"'>X</button>" +
                        "<h3>Create Table " + tableName + "</h3>" +
                        "<textarea class='modal-textarea'>" + sql + "</textarea>" +
                        "</div>";
                    modal.style.display = "block";
                    
                    modal.onclick = function(e) {
                        if(e.target === modal) modal.style.display = "none";
                    };
                } else {
                    alert(sql);
                }
            } catch (e) {
                console.error("Error parsing JSON response", e);
            }
        });
    }

});