<?php
error_reporting(0);
require_once "lib.php";

/**
 * Membangun string definisi kolom SQL
 */
function buildColumnDefinition($col) {
    $definition = "`" . $col['Field'] . "` " . $col['Type'];
    $definition .= ($col['Null'] === 'NO') ? " NOT NULL" : " NULL";

    if ($col['Default'] !== null) {
        if (in_array(strtoupper($col['Default']), array('CURRENT_TIMESTAMP', 'NULL'))) {
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

    $host1 = get_post('host1', 'localhost');
    $port1 = get_post('port1', 3306);
    $db1   = get_post('db1');
    $user1 = get_post('user1', 'root');
    $pass1 = get_post('pass1', '');

    $host2 = get_post('host2', 'localhost');
    $port2 = get_post('port2', 3306);
    $db2   = get_post('db2');
    $user2 = get_post('user2', 'root');
    $pass2 = get_post('pass2', '');

    $table = get_post('tb');

    try {
        $db_conn1 = get_db_connection($host1, $port1, $db1, $user1, $pass1);
        $db_conn2 = get_db_connection($host2, $port2, $db2, $user2, $pass2);
    } catch (PDOException $e) {
        echo json_encode(array('error' => "Koneksi database gagal: " . $e->getMessage()));
        exit;
    }

    /**
     * PERBAIKAN: Fungsi dibungkus try-catch agar tidak crash jika tabel tidak ada
     */
    $get_cols = function($conn, $tbl) {
        $cols = array();
        try {
            $stmt = $conn->prepare("SHOW COLUMNS FROM `$tbl`");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as $row) {
                $cols[$row['Field']] = $row;
            }
        } catch (Exception $e) {
            // Tabel tidak ditemukan, biarkan $cols tetap array kosong
        }
        return $cols;
    };

    $cols1 = $get_cols($db_conn1, $table);
    $cols2 = $get_cols($db_conn2, $table);

    $sql_to_sync_db1 = array(); 
    $sql_to_sync_db2 = array(); 

    $exists1 = !empty($cols1);
    $exists2 = !empty($cols2);

    if ($exists1 && !$exists2) {
        // Hanya di DB1 -> Buat di DB2
        $stmt = $db_conn1->prepare("SHOW CREATE TABLE `$table` ");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $sql_to_sync_db2[] = $row['Create Table'] . ";";
    } elseif (!$exists1 && $exists2) {
        // Hanya di DB2 -> Buat di DB1
        $stmt = $db_conn2->prepare("SHOW CREATE TABLE `$table` ");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $sql_to_sync_db1[] = $row['Create Table'] . ";";
    } elseif ($exists1 && $exists2) {
        // Bandingkan kolom jika keduanya ada (Logika ALTER tetap sama)
        
        // Cek apa yang harus ditambah/diubah di DB2 agar mirip DB1
        foreach ($cols1 as $name => $def1) {
            if (!isset($cols2[$name])) {
                $sql_to_sync_db2[] = "ALTER TABLE `$table` ADD COLUMN " . buildColumnDefinition($def1) . ";";
            } else {
                if (serialize($def1) !== serialize($cols2[$name])) {
                    $sql_to_sync_db2[] = "ALTER TABLE `$table` MODIFY COLUMN " . buildColumnDefinition($def1) . ";";
                }
            }
        }
        // Hapus kolom di DB2 yang tidak ada di DB1
        foreach ($cols2 as $name => $def2) {
            if (!isset($cols1[$name])) {
                $sql_to_sync_db2[] = "ALTER TABLE `$table` DROP COLUMN `$name`;";
            }
        }

        // --- Sebaliknya (DB2 ke DB1) ---
        foreach ($cols2 as $name => $def2) {
            if (!isset($cols1[$name])) {
                $sql_to_sync_db1[] = "ALTER TABLE `$table` ADD COLUMN " . buildColumnDefinition($def2) . ";";
            } else {
                if (serialize($def2) !== serialize($cols1[$name])) {
                    $sql_to_sync_db1[] = "ALTER TABLE `$table` MODIFY COLUMN " . buildColumnDefinition($def2) . ";";
                }
            }
        }
        foreach ($cols1 as $name => $def1) {
            if (!isset($cols2[$name])) {
                $sql_to_sync_db1[] = "ALTER TABLE `$table` DROP COLUMN `$name`;";
            }
        }
    } else {
        // Keduanya tidak ada
        $sql_to_sync_db1[] = "-- Table $table not found in both databases.";
        $sql_to_sync_db2[] = "-- Table $table not found in both databases.";
    }

    echo json_encode(array(
        'to_sync_db1' => $sql_to_sync_db1,
        'to_sync_db2' => $sql_to_sync_db2
    ));
}