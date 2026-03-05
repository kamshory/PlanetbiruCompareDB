<?php
require_once "lib.php";
$langCode = get_lang_code();
$lang = new Language($langCode);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $lang->get('app_title'); ?></title>
  <script>
    const appTranslations = <?php echo json_encode($lang->getAll()); ?>;

    function changeLanguage(lang) {
      document.cookie = "lang=" + lang + "; path=/; max-age=" + (365 * 24 * 60 * 60);
      location.reload();
    }
  </script>
  <script src="assets/script.js"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <div class="all">
    <div class="input-bar">
      <form name="form1" method="post" action="">
        <input type="button" id="setting" value="<?php echo $lang->get('configuration'); ?>">
        <input type="submit" name="list-tables" id="list-tables" value="<?php echo $lang->get('list_tables'); ?>">
        <select id="language-selector" onchange="changeLanguage(this.value)">
          <option value="en" <?php echo $langCode == 'en' ? 'selected' : ''; ?>>English</option>
          <option value="id" <?php echo $langCode == 'id' ? 'selected' : ''; ?>>Indonesia</option>
        </select>

        <div id="config-modal" class="dialog-modal" style="display:none">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="modal-close-btn close-modal">&times;</button>
              <h3><?php echo $lang->get('database_configuration'); ?></h3>
            </div>
            <div class="modal-body">
              <div class="config-wrapper">
                <div class="config-col">
                  <h4><?php echo $lang->get('database_1'); ?></h4>
                  <table class="config-table" width="100%">
                    <tr>
                      <td><?php echo $lang->get('driver'); ?></td>
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
                      <td><?php echo $lang->get('host'); ?></td>
                      <td><input class="input-host" type="text" name="host1" id="host1" /></td>
                    </tr>
                    <tr>
                      <td><?php echo $lang->get('port'); ?></td>
                      <td><input class="input-port" type="number" name="port1" id="port1" /></td>
                    </tr>
                    <tr>
                      <td><?php echo $lang->get('database'); ?></td>
                      <td><input class="input-db" type="text" name="db1" id="db1" /></td>
                    </tr>
                    <tr>
                      <td><?php echo $lang->get('username'); ?></td>
                      <td><input class="input-user" type="text" name="user1" id="user1" /></td>
                    </tr>
                    <tr>
                      <td><?php echo $lang->get('password'); ?></td>
                      <td><input class="input-pass" type="password" name="pass1" id="pass1" /></td>
                    </tr>
                  </table>
                </div>

                <div class="swap-col">
                  <a href="#" class="swap-control" title="<?php echo $lang->get('swap_configurations'); ?>">&#8646;</a>
                </div>

                <div class="config-col">
                  <h4><?php echo $lang->get('database_2'); ?></h4>
                  <table class="config-table" width="100%">
                    <tr>
                      <td><?php echo $lang->get('driver'); ?></td>
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
                      <td><?php echo $lang->get('host'); ?></td>
                      <td><input class="input-host" type="text" name="host2" id="host2" /></td>
                    </tr>
                    <tr>
                      <td><?php echo $lang->get('port'); ?></td>
                      <td><input class="input-port" type="number" name="port2" id="port2" /></td>
                    </tr>
                    <tr>
                      <td><?php echo $lang->get('database'); ?></td>
                      <td><input class="input-db" type="text" name="db2" id="db2" /></td>
                    </tr>
                    <tr>
                      <td><?php echo $lang->get('username'); ?></td>
                      <td><input class="input-user" type="text" name="user2" id="user2" /></td>
                    </tr>
                    <tr>
                      <td><?php echo $lang->get('password'); ?></td>
                      <td><input class="input-pass" type="password" name="pass2" id="pass2" /></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn close-modal btn-secondary"><?php echo $lang->get('ok'); ?></button>
            </div>
          </div>
        </div>

      </form>
    </div>

    <div class="wrapper">

      <div class="db-area db1-area">
        <h3><?php echo $lang->get('database_1'); ?>: <span id="text_db1_name" class="text_db_name"></span></h3>
        <div class="table-container table1-container"></div>
        <div class="field-container field1-container"></div>
      </div>
      <div class="db-area db2-area">
        <h3><?php echo $lang->get('database_2'); ?>: <span id="text_db2_name" class="text_db_name"></span></h3>
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