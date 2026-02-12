<?php
function removequote($input) {
    if (is_array($input)) {
        foreach ($input as $key => $val) {
            $input[$key] = removequote($val);
        }
        return $input;
    }
    return str_replace(array('"', "'", "`"), "", $input);
}

function get_post($key, $default = '') {
    $val = (isset($_POST[$key]) && strlen(trim($_POST[$key])) > 0) ? trim($_POST[$key]) : $default;
    return removequote($val);
}

function get_db_connection($host, $port, $dbname, $user, $pass) {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $pdo->exec("SET time_zone='" . date('P') . "'");
    return $pdo;
}
?>