class CompareDB {
    constructor() {
        // DOM Elements
        this.form = document.forms["form1"];
        this.settingButton = document.getElementById("setting");
        this.settingModal = document.getElementById("config-modal");
        this.swapControl = document.querySelector(".swap-control");
        this.listTablesBtn = document.getElementById("list-tables");
        this.db1NameEl = document.getElementById("text_db1_name");
        this.db2NameEl = document.getElementById("text_db2_name");
        this.tableContainer1 = document.querySelector(".table1-container");
        this.tableContainer2 = document.querySelector(".table2-container");
        this.fieldContainer1 = document.querySelector(".field1-container");
        this.fieldContainer2 = document.querySelector(".field2-container");
        this.actionContainer = document.getElementById("action-container");
        this.dynamicModal = document.getElementById("dynamic-modal");

        // Bind initial events
        this.bindEvents();
    }

    // Binds event listeners to static elements on the page
    bindEvents() {
        // Toggle Settings Modal
        if (this.settingButton && this.settingModal) {
            this.settingButton.addEventListener("click", () => {
                this.settingModal.style.display = "block";
            });

            const closeButtons = this.settingModal.querySelectorAll(".close-modal, .btn-secondary");
            closeButtons.forEach(btn => {
                btn.addEventListener("click", () => {
                    this.settingModal.style.display = "none";
                });
            });

            window.addEventListener("click", (event) => {
                if (event.target === this.settingModal) {
                    this.settingModal.style.display = "none";
                }
            });
        }

        // Swap DB Config
        if (this.swapControl) {
            this.swapControl.addEventListener("click", (e) => {
                e.preventDefault();
                this.swapDbConfig();
            });
        }

        // List Tables Action
        if (this.listTablesBtn) {
            this.listTablesBtn.addEventListener("click", (e) => {
                e.preventDefault();
                this.listTables();
            });
        }
    }

    // Swap Database Configuration values
    swapDbConfig() {
        const fields = ["driver", "host", "port", "db", "user", "pass"];
        fields.forEach(field => {
            const el1 = document.getElementById(field + "1");
            const el2 = document.getElementById(field + "2");
            if (el1 && el2) {
                [el1.value, el2.value] = [el2.value, el1.value]; // Modern swap
            }
        });
    }

    // Get all form data
    _getFormData() {
        const data = {};
        const elements = this.form.elements;
        for (let i = 0; i < elements.length; i++) {
            const el = elements[i];
            if (el.name && (el.type !== "submit" && el.type !== "button")) {
                data[el.name] = el.value;
            }
        }
        return data;
    }

    // AJAX helper
    _ajaxPost(url, data, callback) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                callback(xhr.responseText);
            }
        };
        const params = Object.keys(data)
            .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(data[key])}`)
            .join("&");
        xhr.send(params);
    }

    // Fetch and display the list of tables
    listTables() {
        const data = this._getFormData();

        if (this.db1NameEl) this.db1NameEl.innerText = data.db1;
        if (this.db2NameEl) this.db2NameEl.innerText = data.db2;

        this._ajaxPost("ajax-show-table.php", data, (response) => {
            if (!response) return;
            try {
                const res = JSON.parse(response);
                if (res.error) {
                    this.showMessage(res.error, "Connection Error");
                    return;
                }
                this.renderTableList(res);
            } catch (e) {
                console.error("Error parsing JSON response", e);
            }
        });
    }

    // Render the list of tables in the UI
    renderTableList(res) {
        this.tableContainer1.innerHTML = "";
        this.tableContainer2.innerHTML = "";
        this.fieldContainer1.innerHTML = "";
        this.fieldContainer2.innerHTML = "";
        this.actionContainer.innerHTML = "";

        const diffs = res.diftbl || [];

        const createList = (dbData, dbKey, container) => {
            if (!dbData || !dbData.data) return;

            const ul = document.createElement("ul");
            ul.className = "table-list";

            dbData.data.forEach(row => {
                const li = document.createElement("li");
                li.innerText = row.Name;
                li.dataset.table = row.Name;

                if (diffs.includes(row.Name)) {
                    li.classList.add("diff");
                }

                // Click to load fields
                li.addEventListener("click", () => {
                    document.querySelectorAll(".table-container li.active").forEach(el => el.classList.remove("active"));

                    li.classList.add("active");
                    const counterpartContainer = (dbKey === "db1" ? this.tableContainer2 : this.tableContainer1);
                    const counterpart = counterpartContainer.querySelector(`li[data-table='${row.Name}']`);
                    if (counterpart) counterpart.classList.add("active");

                    this.loadFields(row.Name);
                });

                // Right click to show Create Table SQL
                li.addEventListener("contextmenu", (e) => {
                    e.preventDefault();
                    this.showCreateTable(dbKey, e.target.closest('li').dataset.table);
                });

                ul.appendChild(li);
            });
            container.appendChild(ul);
        };

        createList(res.db1, "db1", this.tableContainer1);
        createList(res.db2, "db2", this.tableContainer2);
    }

    // Load fields for a specific table
    loadFields(tableName) {
        const data = this._getFormData();
        data.tb = tableName;

        this._ajaxPost("ajax-show-field.php", data, (response) => {
            if (!response) return;
            try {
                const res = JSON.parse(response);
                if (res.error) {
                    this.showMessage(res.error, "Connection Error");
                    return;
                }
                this.renderFields(res);
            } catch (e) {
                console.error("Error parsing JSON response", e);
            }
        });
    }

    // Render the field comparison tables
    renderFields(res) {
        this.fieldContainer1.innerHTML = this._createFieldTable(res.tb1);
        this.fieldContainer2.innerHTML = this._createFieldTable(res.tb2);

        const hasDiff = this._highlightFieldDiffs();
        const tableName = (res.tb1 && res.tb1.name) || (res.tb2 && res.tb2.name);

        if (this.actionContainer && tableName) {
            if (hasDiff) {
                this.actionContainer.innerHTML = `<button id="generate-sql-btn">Generate Sync SQL for \`${tableName}\`</button>`;
                document.getElementById("generate-sql-btn").addEventListener("click", () => {
                    this.generateAlterSql(tableName);
                });
            } else {
                this.actionContainer.innerHTML = '<div class="message-identical">Tables are identical.</div>';
            }
        } else if (this.actionContainer) {
            this.actionContainer.innerHTML = "";
        }
    }

    // Helper to create the HTML for a field table
    _createFieldTable(tbData) {
        if (!tbData || !tbData.coldata) return "Table not found in this database.";

        let html = "<table border='1' cellspacing='0' cellpadding='3' width='100%'>";
        html += "<thead><tr>";
        if (tbData.colcaption) {
            tbData.colcaption.forEach(cap => html += `<th>${cap}</th>`);
        }
        html += "</tr></thead><tbody>";

        const keys = ["Field", "Type", "Null", "Key", "Default", "Extra"];
        for (const fieldName in tbData.coldata) {
            const row = tbData.coldata[fieldName];
            html += `<tr data-field='${fieldName}'>`;
            keys.forEach(k => {
                html += `<td>${row[k] !== null ? row[k] : "NULL"}</td>`;
            });
            html += "</tr>";
        }
        html += "</tbody></table>";
        return html;
    }

    // Helper to highlight differences between field tables
    _highlightFieldDiffs() {
        const rows1 = this.fieldContainer1.querySelectorAll("tbody tr");
        const rows2 = this.fieldContainer2.querySelectorAll("tbody tr");

        const map1 = new Map(Array.from(rows1).map(row => [row.dataset.field, row]));
        const map2 = new Map(Array.from(rows2).map(row => [row.dataset.field, row]));

        let diffFound = false;

        for (const [field, row1] of map1.entries()) {
            if (!map2.has(field)) {
                row1.classList.add("row-missing"); // Missing in DB2
                diffFound = true;
            } else {
                const row2 = map2.get(field);
                if (row1.innerHTML !== row2.innerHTML) {
                    row1.classList.add("row-diff");
                    row2.classList.add("row-diff");
                    diffFound = true;
                }
            }
        }

        for (const [field, row2] of map2.entries()) {
            if (!map1.has(field)) {
                row2.classList.add("row-missing"); // Missing in DB1
                diffFound = true;
            }
        }
        return diffFound;
    }

    // Generate ALTER SQL statements
    generateAlterSql(tableName) {
        const data = this._getFormData();
        data.tb = tableName;

        const btn = document.getElementById("generate-sql-btn");
        if (btn) {
            btn.disabled = true;
            btn.textContent = "Generating...";
        }

        this._ajaxPost("ajax-generate-alter.php", data, (response) => {
            if (btn) {
                btn.disabled = false;
                btn.textContent = `Generate Sync SQL for \`${tableName}\``;
            }

            try {
                const res = JSON.parse(response);
                if (res.error) {
                    this.showMessage("An error occurred: " + res.error, "Error");
                    return;
                }
                this.showAlterSqlModal(tableName, res);
            } catch (e) {
                console.error("Error parsing JSON for ALTER SQL", e);
                this.showMessage("Could not process the request to generate SQL.", "Error");
            }
        });
    }

    // Show modal with generated ALTER SQL
    showAlterSqlModal(tableName, sqlData) {
        const sql1 = sqlData.to_sync_db1.join("\n") || "-- No changes needed --";
        const sql2 = sqlData.to_sync_db2.join("\n") || "-- No changes needed --";

        const db1Name = document.getElementById("db1").value;
        const db2Name = document.getElementById("db2").value;

        const modalContent = `
            <div class="modal-content modal-content-large">
                <div class="modal-header">
                    <button class="modal-close-btn close-modal">&times;</button>
                    <h3>Synchronize SQL for Table \`${tableName}\`</h3>
                </div>
                <div class="modal-body">
                    <div class="modal-flex-container">
                        <div class="modal-flex-item">
                            <h4>Make \`${db1Name}\` like \`${db2Name}\`</h4>
                            <p>Run this SQL on Database 1:</p>
                            <textarea class="modal-textarea" id="sql-db1" readonly>${sql1}</textarea>
                            <div class="modal-action-bar">
                                <button class="btn btn-primary copy-btn" data-db="db1">Copy</button>
                                <button class="btn btn-success execute-btn" data-db="db1">Execute on DB1</button>
                            </div>
                        </div>
                        <div class="modal-flex-item">
                            <h4>Make \`${db2Name}\` like \`${db1Name}\`</h4>
                            <p>Run this SQL on Database 2:</p>
                            <textarea class="modal-textarea" id="sql-db2" readonly>${sql2}</textarea>
                            <div class="modal-action-bar">
                                <button class="btn btn-primary copy-btn" data-db="db2">Copy</button>
                                <button class="btn btn-success execute-btn" data-db="db2">Execute on DB2</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary close-modal">Close</button>
                </div>
            </div>
        `;

        this.dynamicModal.innerHTML = modalContent;
        this.dynamicModal.style.display = "block";

        // Bind events for the new modal content
        this.dynamicModal.querySelectorAll(".close-modal").forEach(btn => {
            btn.addEventListener("click", () => this.dynamicModal.style.display = "none");
        });

        this.dynamicModal.querySelectorAll(".copy-btn").forEach(btn => {
            btn.addEventListener("click", (e) => {
                const textarea = e.target.closest(".modal-flex-item").querySelector("textarea");
                navigator.clipboard.writeText(textarea.value).then(() => {
                    this.showMessage("SQL copied to clipboard.", "Success");
                }).catch(err => {
                    console.error("Failed to copy text: ", err);
                    this.showMessage("Failed to copy SQL.", "Error");
                });
            });
        });

        this.dynamicModal.querySelectorAll(".execute-btn").forEach(btn => {
            btn.addEventListener("click", (e) => {
                const db = e.target.dataset.db;
                const sql = document.getElementById(`sql-${db}`).value;
                if (!sql || sql.trim().startsWith("--")) {
                    this.showMessage("Nothing to execute.", "Warning");
                    return;
                }
                this.showConfirm(`Are you sure you want to execute this query on ${db.toUpperCase()}?`, () => {
                    this.executeQuery(db, sql);
                });
            });
        });

        this.dynamicModal.addEventListener("click", (e) => {
            if (e.target === this.dynamicModal) {
                this.dynamicModal.style.display = "none";
            }
        });
    }

    // Execute a query on the target database
    executeQuery(targetDb, sql) {
        const data = this._getFormData();
        data.target_db = targetDb;
        data.sql = sql;

        const btn = this.dynamicModal.querySelector(`.execute-btn[data-db='${targetDb}']`);
        let originalText = "";
        if (btn) {
            originalText = btn.innerText;
            btn.disabled = true;
            btn.innerText = "Executing...";
        }

        this._ajaxPost("ajax-execute-query.php", data, (response) => {
            if (btn) {
                btn.disabled = false;
                btn.innerText = originalText;
            }
            try {
                const res = JSON.parse(response);
                if (res.success) {
                    this.showMessage("Query executed successfully. Please 'List Tables' again to see changes.", "Success");
                } else {
                    this.showMessage("Error: " + res.error, "Error");
                }
            } catch (e) {
                console.error("Error parsing JSON response", e);
                this.showMessage("Failed to execute query.", "Error");
            }
        });
    }

    // Show a confirmation dialog
    showConfirm(message, callback) {
        const confirmModal = document.createElement("div");
        confirmModal.className = "dialog-modal dialog-modal-top";
        confirmModal.style.display = "block";

        confirmModal.innerHTML = `
            <div class='modal-content modal-content-medium modal-content-confirm'>
                <div class='modal-header'>
                    <button class='modal-close-btn close-modal'>&times;</button>
                    <h3>Confirmation</h3>
                </div>
                <div class='modal-body'>${message}</div>
                <div class='modal-footer'>
                    <button class='btn btn-success confirm-yes btn-confirm-yes'>Yes</button>
                    <button class='btn btn-secondary confirm-no btn-confirm-no'>No</button>
                </div>
            </div>`;

        document.body.appendChild(confirmModal);

        const close = () => document.body.removeChild(confirmModal);

        confirmModal.querySelector(".close-modal").addEventListener("click", close);
        confirmModal.querySelector(".confirm-no").addEventListener("click", close);
        confirmModal.querySelector(".confirm-yes").addEventListener("click", () => {
            close();
            callback();
        });
        confirmModal.addEventListener("click", (e) => {
            if (e.target === confirmModal) close();
        });
    }

    // Show a simple message dialog
    showMessage(message, title = "Information") {
        this.dynamicModal.innerHTML = `
            <div class='modal-content modal-content-medium modal-content-message'>
                <div class='modal-header'>
                    <button class='modal-close-btn close-modal'>&times;</button>
                    <h3>${title}</h3>
                </div>
                <div class='modal-body'>${message}</div>
                <div class='modal-footer'>
                    <button class='btn btn-secondary close-modal'>OK</button>
                </div>
            </div>`;
        this.dynamicModal.style.display = "block";

        this.dynamicModal.querySelectorAll(".close-modal").forEach(btn => {
            btn.addEventListener("click", () => this.dynamicModal.style.display = "none");
        });
        this.dynamicModal.addEventListener("click", (e) => {
            if (e.target === this.dynamicModal) {
                this.dynamicModal.style.display = "none";
            }
        });
    }

    // Show the 'CREATE TABLE' SQL in a modal
    showCreateTable(dbKey, tableName) {
        const data = this._getFormData();
        data.db = data[dbKey];
        data.host = data[`host${dbKey === "db1" ? "1" : "2"}`];
        data.port = data[`port${dbKey === "db1" ? "1" : "2"}`];
        data.user = data[`user${dbKey === "db1" ? "1" : "2"}`];
        data.pass = data[`pass${dbKey === "db1" ? "1" : "2"}`];
        data.tb = tableName;

        this._ajaxPost("ajax-show-create-table.php", data, (response) => {
            try {
                const res = JSON.parse(response);
                if (res.error) {
                    this.showMessage(res.error, "Error");
                    return;
                }
                const sql = res.sql.db1 || res.sql.db2;
                if (!sql) {
                    this.showMessage(`Could not retrieve CREATE TABLE statement for '${tableName}'. It might not exist in this database.`, "Not Found");
                    return;
                }

                this.dynamicModal.innerHTML = `
                    <div class='modal-content modal-content-medium'>
                        <div class="modal-header">
                            <button class="modal-close-btn close-modal">&times;</button>
                            <h3>Create Table ${tableName}</h3>
                        </div>
                        <div class="modal-body">
                            <textarea class="modal-textarea" spellcheck="false" readonly>${sql}</textarea>
                        </div>
                        <div class='modal-footer'>
                            <button class="btn btn-primary copy-create-sql">Copy</button>
                            <button class="btn btn btn-secondary close-modal">Close</button>
                        </div>
                    </div>`;
                this.dynamicModal.style.display = "block";

                // Bind events for new modal
                this.dynamicModal.querySelectorAll(".close-modal").forEach(btn => {
                    btn.addEventListener("click", () => this.dynamicModal.style.display = "none");
                });
                this.dynamicModal.querySelector(".copy-create-sql").addEventListener("click", (e) => {
                    const textarea = e.target.closest(".modal-content").querySelector("textarea");
                    navigator.clipboard.writeText(textarea.value);
                    this.dynamicModal.style.display = 'none';
                    this.showMessage("SQL copied to clipboard.", "Success");
                });
                this.dynamicModal.addEventListener("click", (e) => {
                    if (e.target === this.dynamicModal) {
                        this.dynamicModal.style.display = "none";
                    }
                });

            } catch (e) {
                console.error("Error parsing JSON response", e);
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", function () {
    new CompareDB();
});