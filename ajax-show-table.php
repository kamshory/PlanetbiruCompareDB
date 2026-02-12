<?php

function removequote($input) {
    return str_replace(array('"', "'", "`"), "", $input);
}
/**
 * Fungsi pembantu untuk mengambil data POST dengan nilai default
 * Kompatibel dengan PHP 5.2 ke atas
 */
function get_post($key, $default = '') {
    return (isset($_POST[$key]) && strlen(trim($_POST[$key])) > 0)
        ? trim($_POST[$key])
        : $default;
}
// Membersihkan semua input $_POST dari kutipan
if (!empty($_POST)) {
    foreach ($_POST as $key => $val) {
        $_POST[$key] = removequote($val);
    }
}


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

    // Koneksi ke database pertama
    try {
        $database1 = new PDO("mysql:host=$host1;port=$port1;dbname=$db1", $user1, $pass1);
        $database1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $database1->exec("SET time_zone='" . date('P') . "'");
        $sdb1 = true;
    } catch (PDOException $e) {
        error_log("Database 1 connection error: " . $e->getMessage());
    }

    // Koneksi ke database kedua
    try {
        $database2 = new PDO("mysql:host=$host2;port=$port2;dbname=$db2", $user2, $pass2);
        $database2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $database2->exec("SET time_zone='" . date('P') . "'");
        $sdb2 = true;
    } catch (PDOException $e) {
        error_log("Database 2 connection error: " . $e->getMessage());
    }

    // Jika salah satu database tidak terhubung, keluarkan JSON respons
    if (!$sdb1 || !$sdb2) {
        $output = array(
            "db1" => array("connect" => true, "selectdb" => $sdb1, "data" => null),
            "db2" => array("connect" => true, "selectdb" => $sdb2, "data" => null),
            "diftbl" => null
        );
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
