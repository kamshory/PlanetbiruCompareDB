<?php

/**
 * Fungsi pembantu untuk mengambil data POST dengan nilai default dan membersihkannya.
 */
function get_post($key, $default = '') {
    return (isset($_POST[$key]) && strlen(trim($_POST[$key])) > 0)
        ? trim(str_replace(array('"', "'", "`"), "", $_POST[$key]))
        : $default;
}

/**
 * Membangun string definisi kolom SQL dari baris hasil SHOW COLUMNS.
 * @param array $col Array asosiatif untuk satu kolom.
 * @return string Fragmen definisi SQL untuk kolom tersebut.
 */
function buildColumnDefinition($col) {
    $definition = "`" . $col['Field'] . "` " . $col['Type'];

    if ($col['Null'] === 'NO') {
        $definition .= " NOT NULL";
    } else {
        $definition .= " NULL";
    }

    if ($col['Default'] !== null) {
        if (in_array(strtoupper($col['Default']), ['CURRENT_TIMESTAMP', 'NULL'])) {
             $definition .= " DEFAULT " . $col['Default'];
        } else {
             $definition .= " DEFAULT '" . addslashes($col['Default']) . "'";
        }
    } else if ($col['Null'] === 'YES') {
        $definition .= " DEFAULT NULL";
    }

    if (!empty($col['Extra'])) {
        $definition .= " " . strtoupper($col['Extra']);
    }
    
    return $definition;
}

if (isset($_POST['db1']) && isset($_POST['db2']) && isset($_POST['tb'])) {
    header("Content-Type: application/json");

    // Konfigurasi DB1
    $host1 = get_post('host1', 'localhost');
    $port1 = get_post('port1', 3306);
    $db1   = get_post('db1');
    $user1 = get_post('user1', 'root');
    $pass1 = get_post('pass1', '');

    // Konfigurasi DB2
    $host2 = get_post('host2', 'localhost');
    $port2 = get_post('port2', 3306);
    $db2   = get_post('db2');
    $user2 = get_post('user2', 'root');
    $pass2 = get_post('pass2', '');

    $table = get_post('tb');

    try {
        $db_conn1 = new PDO("mysql:host=$host1;port=$port1;dbname=$db1", $user1, $pass1, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $db_conn2 = new PDO("mysql:host=$host2;port=$port2;dbname=$db2", $user2, $pass2, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } catch (PDOException $e) {
        echo json_encode(['error' => "Koneksi database gagal: " . $e->getMessage()]);
        exit;
    }

    // Fungsi untuk mengambil kolom dan mengindeksnya berdasarkan nama field
    $get_cols = function($conn, $tbl) {
        $stmt = $conn->prepare("SHOW COLUMNS FROM `$tbl`");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $cols = [];
        foreach ($results as $row) {
            $cols[$row['Field']] = $row;
        }
        return $cols;
    };

    $cols1 = $get_cols($db_conn1, $table);
    $cols2 = $get_cols($db_conn2, $table);

    $sql_to_sync_db1 = []; // Perintah untuk membuat DB1 sama seperti DB2
    $sql_to_sync_db2 = []; // Perintah untuk membuat DB2 sama seperti DB1

    // --- Bandingkan DB1 -> DB2 (Buat query untuk dijalankan di DB2) ---
    // Cari field di DB1 yang tidak ada di DB2 (ADD di DB2)
    foreach ($cols1 as $field_name => $col_def) {
        if (!isset($cols2[$field_name])) {
            $sql_to_sync_db2[] = "ALTER TABLE `$table` ADD COLUMN " . buildColumnDefinition($col_def) . ";";
        }
    }
    // Cari field di DB2 yang tidak ada di DB1 (DROP dari DB2)
    foreach ($cols2 as $field_name => $col_def) {
        if (!isset($cols1[$field_name])) {
            $sql_to_sync_db2[] = "ALTER TABLE `$table` DROP COLUMN `$field_name`;";
        }
    }
    // Cari field yang sama tapi definisinya beda (MODIFY di DB2)
    foreach ($cols1 as $field_name => $col_def1) {
        if (isset($cols2[$field_name])) {
            $col_def2 = $cols2[$field_name];
            if (
                $col_def1['Type']    !== $col_def2['Type'] ||
                $col_def1['Null']    !== $col_def2['Null'] ||
                $col_def1['Default'] !== $col_def2['Default'] ||
                $col_def1['Extra']   !== $col_def2['Extra']
            ) {
                $sql_to_sync_db2[] = "ALTER TABLE `$table` MODIFY COLUMN " . buildColumnDefinition($col_def1) . ";";
            }
        }
    }

    // --- Bandingkan DB2 -> DB1 (Buat query untuk dijalankan di DB1) ---
    // Cari field di DB2 yang tidak ada di DB1 (ADD di DB1)
    foreach ($cols2 as $field_name => $col_def) {
        if (!isset($cols1[$field_name])) {
            $sql_to_sync_db1[] = "ALTER TABLE `$table` ADD COLUMN " . buildColumnDefinition($col_def) . ";";
        }
    }
    // Cari field di DB1 yang tidak ada di DB2 (DROP dari DB1)
    foreach ($cols1 as $field_name => $col_def) {
        if (!isset($cols2[$field_name])) {
            $sql_to_sync_db1[] = "ALTER TABLE `$table` DROP COLUMN `$field_name`;";
        }
    }
    // Cari field yang sama tapi definisinya beda (MODIFY di DB1)
    foreach ($cols2 as $field_name => $col_def2) {
        if (isset($cols1[$field_name])) {
            $col_def1 = $cols1[$field_name];
            if (
                $col_def1['Type']    !== $col_def2['Type'] ||
                $col_def1['Null']    !== $col_def2['Null'] ||
                $col_def1['Default'] !== $col_def2['Default'] ||
                $col_def1['Extra']   !== $col_def2['Extra']
            ) {
                $sql_to_sync_db1[] = "ALTER TABLE `$table` MODIFY COLUMN " . buildColumnDefinition($col_def2) . ";";
            }
        }
    }

    echo json_encode([
        'to_sync_db1' => $sql_to_sync_db1,
        'to_sync_db2' => $sql_to_sync_db2
    ]);
}

?>