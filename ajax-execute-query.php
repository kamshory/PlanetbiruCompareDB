<?php
error_reporting(0);
require_once "lib.php";


if (isset($_POST['target_db']) && isset($_POST['sql'])) {
    header("Content-Type: application/json");

    $target = $_POST['target_db']; // 'db1' atau 'db2'
    $suffix = ($target === 'db1') ? '1' : '2';

    $host = get_post('host' . $suffix, 'localhost');
    $port = get_post('port' . $suffix, 3306);
    $db   = get_post('db' . $suffix);
    $user = get_post('user' . $suffix, 'root');
    $pass = get_post('pass' . $suffix, '');
    
    // Ambil SQL mentah (jangan gunakan get_post karena akan menghapus quote)
    $sql = isset($_POST['sql']) ? $_POST['sql'] : '';

    if (empty($sql) || strpos(trim($sql), '--') === 0) {
        echo json_encode(array('success' => false, 'error' => 'No valid SQL to execute'));
        exit;
    }

    try {
        $pdo = get_db_connection($host, $port, $db, $user, $pass);
        // Pisahkan statement berdasarkan titik koma untuk menangani multiple queries
        $statements = explode(";", $sql);
        
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (!empty($stmt)) {
                // Lewati komentar
                if (strpos($stmt, '--') === 0) continue;
                $pdo->exec($stmt);
            }
        }

        echo json_encode(array('success' => true));
    } catch (PDOException $e) {
        echo json_encode(array('success' => false, 'error' => $e->getMessage()));
    } catch (Exception $e) {
        echo json_encode(array('success' => false, 'error' => $e->getMessage()));
    }
}
?>