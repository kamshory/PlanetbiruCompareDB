<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CompareDB version 1.2</title>
  <script src="assets/script.js"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <div class="all">
    <div class="input-bar">
      <form name="form1" method="post" action="">
        <input type="button" id="setting" value="Configuration">
        <input type="submit" name="list-tables" id="list-tables" value="List Tables">
        <div id="config-modal" class="dialog-modal" style="display:none">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="modal-close-btn">&times;</button>
              <h3>Database Configuration</h3>
            </div>
            <div class="modal-body">
              <div class="config-wrapper">
                <div class="config-col">
                  <h4>Database 1</h4>
                  <table class="config-table" width="100%">
                    <tr>
                      <td>Driver</td>
                      <td>
                        <select class="input-host" name="driver1" id="driver1">
                          <option value="mysql">MySQL</option>
                          <option value="pgsql">PostgreSQL</option>
                          <option value="mssql">Microsoft SQL Server</option>
                          <option value="oracle">Oracle</option>
                          <option value="sqlite">SQLite</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Host</td>
                      <td><input class="input-host" type="text" name="host1" id="host1" /></td>
                    </tr>
                    <tr>
                      <td>Port</td>
                      <td><input class="input-port" type="number" name="port1" id="port1" /></td>
                    </tr>
                    <tr>
                      <td>Database</td>
                      <td><input class="input-db" type="text" name="db1" id="db1" /></td>
                    </tr>
                    <tr>
                      <td>Username</td>
                      <td><input class="input-user" type="text" name="user1" id="user1" /></td>
                    </tr>
                    <tr>
                      <td>Password</td>
                      <td><input class="input-pass" type="password" name="pass1" id="pass1" /></td>
                    </tr>
                  </table>
                </div>

                <div class="swap-col">
                  <a href="#" class="swap-control" title="Swap Configurations">&#8646;</a>
                </div>

                <div class="config-col">
                  <h4>Database 2</h4>
                  <table class="config-table" width="100%">
                    <tr>
                      <td>Driver</td>
                      <td>
                        <select class="input-host" name="driver2" id="driver2">
                          <option value="mysql">MySQL</option>
                          <option value="pgsql">PostgreSQL</option>
                          <option value="mssql">Microsoft SQL Server</option>
                          <option value="oracle">Oracle</option>
                          <option value="sqlite">SQLite</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Host</td>
                      <td><input class="input-host" type="text" name="host2" id="host2" /></td>
                    </tr>
                    <tr>
                      <td>Port</td>
                      <td><input class="input-port" type="number" name="port2" id="port2" /></td>
                    </tr>
                    <tr>
                      <td>Database</td>
                      <td><input class="input-db" type="text" name="db2" id="db2" /></td>
                    </tr>
                    <tr>
                      <td>Username</td>
                      <td><input class="input-user" type="text" name="user2" id="user2" /></td>
                    </tr>
                    <tr>
                      <td>Password</td>
                      <td><input class="input-pass" type="password" name="pass2" id="pass2" /></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" onclick="this.closest('.dialog-modal').style.display='none'" class="btn btn-default">OK</button>
            </div>
          </div>
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
      <div id="action-container"></div>
      <div class="clear"></div>
    </div>

  </div>


  <div id="dynamic-modal" class="dialog-modal">

  </div>
</body>

</html>