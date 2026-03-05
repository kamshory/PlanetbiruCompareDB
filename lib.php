<?php
function removequote($input)
{
    if (is_array($input)) {
        foreach ($input as $key => $val) {
            $input[$key] = removequote($val);
        }
        return $input;
    }
    return str_replace(array('"', "'", "`"), "", $input);
}

function get_post($key, $default = '')
{
    $val = (isset($_POST[$key]) && strlen(trim($_POST[$key])) > 0) ? trim($_POST[$key]) : $default;
    return removequote($val);
}

function get_db_connection($driver, $host, $port, $dbname, $user, $pass)
{
    $dsn = "";
    // Construct DSN based on driver
    switch ($driver) {
        case 'pgsql':
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            break;
        case 'mssql':
            $dsn = "sqlsrv:Server=$host,$port;Database=$dbname";
            break;
        case 'mysql':
        default:
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
            break;
    }
    $pdo = new PDO($dsn, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $pdo->exec("SET time_zone='" . date('P') . "'");
    return $pdo;
}

function get_lang_code()
{
    return isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], ['en', 'id']) ? $_COOKIE['lang'] : 'en';
}

class Language
{
    private $data = [];
    public function __construct($lang = 'en')
    {
        $file = __DIR__ . "/languages/$lang.ini";
        if (file_exists($file)) {
            $this->data = parse_ini_file($file);
        } else {
            $file = __DIR__ . "/languages/en.ini";
            if (file_exists($file)) {
                $this->data = parse_ini_file($file);
            }
        }
    }
    public function get($key, $args = [])
    {
        $text = isset($this->data[$key]) ? $this->data[$key] : $key;
        if (!empty($args)) {
            if (!is_array($args)) $args = [$args];
            return vsprintf($text, $args);
        }
        return $text;
    }
    public function getAll()
    {
        return $this->data;
    }
}
