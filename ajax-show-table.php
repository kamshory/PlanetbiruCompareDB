<?php
require_once "lib.php";

if (isset($_POST['db1']) && isset($_POST['db2'])) {
    // Konfigurasi Database 1
    $host1 = get_post('host1', 'localhost');
    $port1 = get_post('port1', 3306);
    $db1   = get_post('db1', '');
    $user1 = get_post('user1', 'root');
    $pass1 = get_post('pass1', '');

    // Konfigurasi Database 2
    $host2 = get_post('host2', 'localhost');
    $port2 = get_post('port2', 3306);
    $db2   = get_post('db2', '');
    $user2 = get_post('user2', 'root');
    $pass2 = get_post('pass2', '');
    $sdb1 = false;
    $sdb2 = false;
    $error = "";

    // Koneksi ke database pertama
    try {
        $database1 = get_db_connection($host1, $port1, $db1, $user1, $pass1);
        $sdb1 = true;
    } catch (PDOException $e) {
        $error .= "Database 1: " . $e->getMessage() . "\n";
        error_log("Database 1 connection error: " . $e->getMessage());
    }

    // Koneksi ke database kedua
    try {
        $database2 = get_db_connection($host2, $port2, $db2, $user2, $pass2);
        $sdb2 = true;
    } catch (PDOException $e) {
        $error .= "Database 2: " . $e->getMessage() . "\n";
        error_log("Database 2 connection error: " . $e->getMessage());
    }

    // Jika salah satu database tidak terhubung, keluarkan JSON respons
    if (!$sdb1 || !$sdb2) {
        echo json_encode(array('error' => trim($error)));
        exit();
    }

    // Fungsi untuk mendapatkan daftar tabel dari database
    function getTables($db) {
        $tables = array();
        try {
            $stmt = $db->query("SHOW TABLE STATUS");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tables[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error fetching tables: " . $e->getMessage());
        }
        return $tables;
    }

    $arr1 = getTables($database1);
    $arr2 = getTables($database2);

    // Fungsi untuk mendapatkan struktur tabel
    function getTableFields($db, $tables) {
        $fields = array();
        foreach ($tables as $table) {
            $tableName = $table['Name'];
            $fieldArr = array();
            try {
                $stmt = $db->prepare("SHOW COLUMNS FROM `$tableName`");
                $stmt->execute();
                while ($dt = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $fieldArr[] = $dt['Field'] . '|' . $dt['Type'] . '|' . $dt['Null'] . '|' . $dt['Key'] . '|' . $dt['Default'] . '|' . $dt['Extra'];
                }
            } catch (Exception $e) {
                error_log("Error fetching fields for table $tableName: " . $e->getMessage());
            }
            $fields[$tableName] = implode(",", $fieldArr);
        }
        return $fields;
    }

    $fields['db1'] = getTableFields($database1, $arr1);
    $fields['db2'] = getTableFields($database2, $arr2);

    // Mencari tabel yang berbeda
    $diftbl = array();
    foreach ($fields['db1'] as $key => $val) {
        if (isset($fields['db2'][$key]) && $val !== $fields['db2'][$key]) {
            $diftbl[] = $key;
        }
    }
    foreach ($fields['db2'] as $key => $val) {
        if (isset($fields['db1'][$key]) && $val !== $fields['db1'][$key]) {
            $diftbl[] = $key;
        }
    }
    $diftbl = array_unique($diftbl);

    // Mengembalikan output JSON
    $output = array(
        "db1" => array("connect" => true, "selectdb" => true, "data" => $arr1),
        "db2" => array("connect" => true, "selectdb" => true, "data" => $arr2),
        "diftbl" => $diftbl
    );

    echo json_encode($output);
}
?>
