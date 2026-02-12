<?php
header("Content-type: application/json");
require_once "lib.php";

/**
 * Fungsi Pengecekan Eksistensi Tabel
 * Menggunakan query ke INFORMATION_SCHEMA agar tidak memicu error 1146
 */
function check_table_exists($pdo, $dbname, $tablename) {
    try {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = :db 
                AND TABLE_NAME = :table";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':db' => $dbname, ':table' => $tablename));
        return ($stmt->fetchColumn() > 0);
    } catch (Exception $e) {
        return false;
    }
}

// Eksekusi jika ada data POST
if (isset($_POST['db']) || isset($_POST['tb'])) {
    
    $table = get_post('tb');
    if (empty($table)) {
        echo json_encode(array('error' => 'Nama tabel kosong'));
        exit;
    }

    try {
        // Koneksi DB 1
        $db1_name = get_post('db1');
        $db_conn1 = get_db_connection(get_post('host1','localhost'), get_post('port1',3306), $db1_name, get_post('user1','root'), get_post('pass1',''));

        // Koneksi DB 2
        $db2_name = get_post('db2');
        $db_conn2 = get_db_connection(get_post('host2','localhost'), get_post('port2',3306), $db2_name, get_post('user2','root'), get_post('pass2',''));

        $result = array();

        // Cek & Ambil SQL DB 1
        if (check_table_exists($db_conn1, $db1_name, $table)) {
            $q = $db_conn1->query("SHOW CREATE TABLE `$table` ");
            $row = $q->fetch(PDO::FETCH_ASSOC);
            $result['db1'] = $row['Create Table'];
        } else {
            $result['db1'] = null; // Tabel tidak ada
        }

        // Cek & Ambil SQL DB 2
        if (check_table_exists($db_conn2, $db2_name, $table)) {
            $q = $db_conn2->query("SHOW CREATE TABLE `$table` ");
            $row = $q->fetch(PDO::FETCH_ASSOC);
            $result['db2'] = $row['Create Table'];
        } else {
            $result['db2'] = null; // Tabel tidak ada
        }

        // Output hasil
        echo json_encode(array(
            'status' => 'success',
            'table'  => $table,
            'exists' => array(
                'db1' => ($result['db1'] !== null),
                'db2' => ($result['db2'] !== null)
            ),
            'sql'    => $result
        ));

    } catch (PDOException $e) {
        echo json_encode(array('error' => "Koneksi/Query Gagal: " . $e->getMessage()));
    }
} else {
    echo json_encode(array('error' => 'No data posted'));
}